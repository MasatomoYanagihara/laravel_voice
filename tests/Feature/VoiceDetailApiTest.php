<?php

namespace Tests\Feature;

use App\Comment;
use App\Voice;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VoiceDetailApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function should_正しい構造のJSONを返却する()
    {
        factory(Voice::class)->create()->each(function ($voice) {
            $voice->comments()->saveMany(factory(Comment::class, 3)->make());
        });
        $voice = Voice::first();

        $response = $this->json('GET', route('voice.show', [
            'id' => $voice->id,
        ]));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $voice->id,
                'url' => $voice->url,
                'owner' => [
                    'name' => $voice->owner->name,
                ],
                'comments' => $voice->comments
                    ->sortByDesc('id')
                    ->map(function ($comment) {
                        return [
                            'author' => [
                                'name' => $comment->author->name,
                            ],
                            'content' => $comment->content,
                        ];
                    })
                    ->all(),
            ]);
    }
}
