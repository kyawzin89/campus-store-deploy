<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Http\Resources\NoteResource;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

/**
 * EXAMPLE RESOURCE: a full CRUD API for notes owned by the logged-in user.
 *
 * Use it as a pattern for your own resources, then delete it (or keep it
 * as an extra; it does NOT count toward your 3 required models).
 *
 *   GET    /api/notes          index    (paginated, only your notes)
 *   POST   /api/notes          store
 *   GET    /api/notes/{note}   show
 *   PUT    /api/notes/{note}   update
 *   DELETE /api/notes/{note}   destroy
 */
class NoteController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $notes = $request->user()
            ->notes()
            ->latest()
            ->paginate(10);

        return NoteResource::collection($notes);
    }

    public function store(StoreNoteRequest $request): NoteResource
    {
        $note = $request->user()->notes()->create($request->validated());

        return new NoteResource($note);
    }

    public function show(Note $note): NoteResource
    {
        Gate::authorize('view', $note);

        return new NoteResource($note);
    }

    public function update(UpdateNoteRequest $request, Note $note): NoteResource
    {
        // Authorization for update happens in UpdateNoteRequest::authorize().
        $note->update($request->validated());

        return new NoteResource($note);
    }

    public function destroy(Note $note): Response
    {
        Gate::authorize('delete', $note);

        $note->delete();

        return response()->noContent();
    }
}
