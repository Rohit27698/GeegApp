<?php

namespace App\Http\Controllers;

use App\Models\Geeg;
use App\Models\GeegUser;
use Illuminate\Http\Request;
use App\Models\User;

class GeegController extends Controller
{
    //
    public function List(Request $request)
    {
        $request_user = $request->user();
        if ($request_user->role !== 'user') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $regex = $request->query('s', '');
        $limit = $request->query('limit', 10);
        $currentpage = $request->query('page', 1);
        $geegs = Geeg::where('status', 'open')
            ->where(function ($query) use ($regex) {
                $query->where('title', 'like', "%$regex%")
                    ->orWhere('discription', 'like', "%$regex%")
                    ->orWhere('subject', 'like', "%$regex%");
            })
            ->skip(($currentpage - 1) * $limit)
            ->take($limit)
            ->get();
            $imageBaseUrl = env('APP_URL') . '/storage/';
            foreach ($geegs as $geeg) {
                if ($geeg->image) {
                    $geeg->image_url = $imageBaseUrl . $geeg->image;
                } else {
                    $geeg->image_url = null;
                }
            }

        return response()->json(['geegs' => $geegs]);
    }
    public function Create(Request $request){
        $request_user = $request->user();
        if ($request_user->role !== 'creator') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
       try {
        $request->validate([
            'title' => 'required|string|max:255',
            'discription' => 'required|string',
            'subject' => 'required|string|max:255',
            'deadline' => 'nullable|date',
            'image' => 'nullable|image|max:2048',
        ]);
        // dd($request->all());
        Geeg::create([
            'title' => $request->title,
            'discription' => $request->discription,
            'subject' => $request->subject,
            'created_by' => $request->user()->id,
            'status' => 'open',
            'deadline' => $request->deadline,
            'image' => $request->file('image') ? $request->file('image')->store('geeg_images') : null,
        ]);}
        catch (\Exception $e) {
            \Log::info('Geeg creation failed: ' . $e->getMessage());
            return response()->json(['error' => 'Geeg creation failed', 'message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Geeg created successfully'], 201);
    }
    public function Delete(Request $request, $id){
        $request_user = $request->user();
        if ($request_user->role !== 'creator') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $geeg = Geeg::find($id);
        if (!$geeg) {
            return response()->json(['error' => 'Geeg not found'], 404);
        }
        if ($geeg->created_by !== $request_user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        if ($geeg->status !== 'open') {
            return response()->json(['error' => 'Geeg cannot be deleted'], 400);
        }
        try {
            $geeg->delete();
        } catch (\Exception $e) {
            \Log::info('Geeg deletion failed: ' . $e->getMessage());
            return response()->json(['error' => 'Geeg deletion failed', 'message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Geeg deleted successfully']);
    }
    public function Apply(Request $request, $id){
        $request_user = $request->user();
        if ($request_user->role !== 'user') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $geeg = Geeg::find($id);
        if (!$geeg) {
            return response()->json(['error' => 'Geeg not found'], 404);
        }
        if ($geeg->status !== 'open') {
            return response()->json(['error' => 'Geeg is not open for applications'], 400);
        }
        try {
            GeegUser::create([
                'geeg_id' => $geeg->id,
                'user_id' => $request_user->id,
            ]);
        } catch (\Exception $e) {
            \Log::info('Application failed: ' . $e->getMessage());
            return response()->json(['error' => 'Application failed', 'message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Applied to Geeg successfully']);
    }
    public function Applications(Request $request, $id){
        $geeg = Geeg::find($id);
        if (!$geeg) {
            return response()->json(['error' => 'Geeg not found'], 404);
        }
        $imageBaseUrl= env('APP_URL') . '/storage/';
        $geeg->image_url = $geeg->image ? $imageBaseUrl . $geeg->image : null;

        return response()->json(['geeg' => $geeg]);
    }
    public function MyApplications(Request $request){
        $request_user = $request->user();
        if ($request_user->role !== 'user') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $myApplications = GeegUser::where('user_id', $request_user->id)->with('geeg')->get();

        return response()->json(['my_applications' => $myApplications]);
    }

    public function MyGeegs(Request $request){
        $request_user = $request->user();
        if ($request_user->role !== 'creator') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $myGeegs = Geeg::where('created_by', $request_user->id)->get();

        return response()->json(['my_geegs' => $myGeegs]);
    }
    public function AssignTo(Request $request, $id){
        $request_user = $request->user();
        if ($request_user->role !== 'creator') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $geeg = Geeg::find($id);
        if (!$geeg) {
            return response()->json(['error' => 'Geeg not found'], 404);
        }
        if ($geeg->created_by !== $request_user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        if ($geeg->status !== 'open') {
            return response()->json(['error' => 'Geeg is not open for assignment'], 400);
        }
        $user_id = $request->input('user_id');
        $user = User::find($user_id);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        try {
            $geeg->assign_to = $user_id;
            $geeg->status = 'assigned';
            $geeg->save();
        } catch (\Exception $e) {
            \Log::info('Assignment failed: ' . $e->getMessage());
            return response()->json(['error' => 'Assignment failed', 'message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Geeg assigned successfully']);
    }
    public function Update(Request $request, $id){
        $request_user = $request->user();
        if ($request_user->role !== 'creator') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $geeg = Geeg::find($id);
        if (!$geeg) {
            return response()->json(['error' => 'Geeg not found'], 404);
        }
        if ($geeg->created_by !== $request_user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        if ($geeg->status !== 'open') {
            return response()->json(['error' => 'Geeg cannot be updated'], 400);
        }
        try {
            $geeg->title = $request->input('title', $geeg->title);
            $geeg->discription = $request->input('discription', $geeg->discription);
            $geeg->subject = $request->input('subject', $geeg->subject);
            $geeg->deadline = $request->input('deadline', $geeg->deadline);
            if ($request->hasFile('image')) {
                $geeg->image = $request->file('image')->store('geeg_images');
            }
            $geeg->save();
        } catch (\Exception $e) {
            \Log::info('Update failed: ' . $e->getMessage());
            return response()->json(['error' => 'Update failed', 'message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Geeg updated successfully']);
    }
}
