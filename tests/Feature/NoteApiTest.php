<?php

namespace Tests\Feature;

use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Example tests for the Note resource. Copy this file as a starting
 * point for testing your own resources.
 */
class NoteApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = Sanctum::actingAs(User::factory()->create());
    }

    public function test_index_lists_only_my_notes_paginated(): void
    {
        Note::factory()->count(3)->for($this->user)->create();
        Note::factory()->count(2)->create(); // someone else's

        $this->getJson('/api/notes')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure(['data' => [['id', 'title', 'body', 'is_pinned']], 'links', 'meta']);
    }

    public function test_store_creates_a_note(): void
    {
        $this->postJson('/api/notes', ['title' => 'Buy milk', 'body' => '2 liters'])
            ->assertCreated()
            ->assertJsonPath('data.title', 'Buy milk');

        $this->assertDatabaseHas('notes', ['title' => 'Buy milk', 'user_id' => $this->user->id]);
    }

    public function test_store_validates_input(): void
    {
        $this->postJson('/api/notes', ['title' => '', 'is_pinned' => 'maybe'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'is_pinned']);
    }

    public function test_show_returns_my_note(): void
    {
        $note = Note::factory()->for($this->user)->create();

        $this->getJson("/api/notes/{$note->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $note->id);
    }

    public function test_update_changes_my_note(): void
    {
        $note = Note::factory()->for($this->user)->create();

        $this->putJson("/api/notes/{$note->id}", ['title' => 'Updated'])
            ->assertOk()
            ->assertJsonPath('data.title', 'Updated');
    }

    public function test_destroy_deletes_my_note(): void
    {
        $note = Note::factory()->for($this->user)->create();

        $this->deleteJson("/api/notes/{$note->id}")->assertNoContent();

        $this->assertModelMissing($note);
    }

    public function test_i_cannot_view_update_or_delete_someone_elses_note(): void
    {
        $note = Note::factory()->create(); // belongs to another user

        $this->getJson("/api/notes/{$note->id}")->assertForbidden();
        $this->putJson("/api/notes/{$note->id}", ['title' => 'Hacked'])->assertForbidden();
        $this->deleteJson("/api/notes/{$note->id}")->assertForbidden();

        $this->assertModelExists($note);
    }

    public function test_a_missing_note_returns_404(): void
    {
        $this->getJson('/api/notes/999')->assertNotFound();
    }
}
