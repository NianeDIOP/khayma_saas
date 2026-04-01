<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\ContactMessage;
use App\Models\Faq;
use App\Models\Module;
use App\Models\Plan;
use App\Models\PlatformSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SiteVitrineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function makeAdmin(): User
    {
        return User::factory()->create(['is_super_admin' => true]);
    }

    // ══════════════════════════════════════════════════════════
    // Public pages
    // ══════════════════════════════════════════════════════════

    #[Test]
    public function home_page_loads(): void
    {
        $this->get('/')->assertStatus(200);
    }

    #[Test]
    public function modules_page_shows_active_modules(): void
    {
        Module::factory()->create(['name' => 'Restaurant', 'is_active' => true]);
        Module::factory()->create(['name' => 'Caché', 'is_active' => false]);

        $this->get('/modules')
             ->assertStatus(200)
             ->assertSee('Restaurant')
             ->assertDontSee('Caché');
    }

    #[Test]
    public function pricing_page_shows_active_plans(): void
    {
        Plan::factory()->create(['name' => 'Starter', 'is_active' => true, 'price_monthly' => 5000]);
        Plan::factory()->create(['name' => 'Hidden', 'is_active' => false]);

        $this->get('/tarifs')
             ->assertStatus(200)
             ->assertSee('Starter')
             ->assertDontSee('Hidden');
    }

    #[Test]
    public function demo_page_loads(): void
    {
        $this->get('/demo')->assertStatus(200);
    }

    #[Test]
    public function contact_page_loads(): void
    {
        $this->get('/contact')->assertStatus(200);
    }

    #[Test]
    public function contact_form_stores_message(): void
    {
        $this->from('/contact')->post('/contact', [
            'name'    => 'Amadou Diop',
            'email'   => 'amadou@test.com',
            'phone'   => '+221770001122',
            'subject' => 'Demande info',
            'message' => 'Je souhaite en savoir plus sur Khayma.',
        ])->assertRedirect('/contact');

        $this->assertDatabaseHas('contact_messages', [
            'name'  => 'Amadou Diop',
            'email' => 'amadou@test.com',
        ]);
    }

    #[Test]
    public function contact_form_validates_required_fields(): void
    {
        $this->post('/contact', [
            'name'    => '',
            'email'   => '',
            'message' => '',
        ])->assertSessionHasErrors(['name', 'email', 'message']);
    }

    #[Test]
    public function faq_page_shows_active_faqs(): void
    {
        Faq::factory()->create(['question' => 'Comment ça marche ?', 'is_active' => true]);
        Faq::factory()->create(['question' => 'Inactive', 'is_active' => false]);

        $this->get('/faq')
             ->assertStatus(200)
             ->assertSee('Comment ça marche ?')
             ->assertDontSee('Inactive');
    }

    #[Test]
    public function blog_page_shows_published_posts(): void
    {
        $author = User::factory()->create();
        BlogPost::factory()->create([
            'title'        => 'Article publié',
            'author_id'    => $author->id,
            'is_published' => true,
            'published_at' => now(),
        ]);
        BlogPost::factory()->create([
            'title'        => 'Brouillon secret',
            'author_id'    => $author->id,
            'is_published' => false,
        ]);

        $this->get('/blog')
             ->assertStatus(200)
             ->assertSee('Article publié')
             ->assertDontSee('Brouillon secret');
    }

    #[Test]
    public function blog_show_displays_published_post(): void
    {
        $author = User::factory()->create();
        $post = BlogPost::factory()->create([
            'title'        => 'Mon article',
            'slug'         => 'mon-article',
            'body'         => '<p>Contenu de test</p>',
            'author_id'    => $author->id,
            'is_published' => true,
            'published_at' => now(),
        ]);

        $this->get('/blog/mon-article')
             ->assertStatus(200)
             ->assertSee('Mon article')
             ->assertSee('Contenu de test');
    }

    #[Test]
    public function blog_show_returns_404_for_unpublished(): void
    {
        $author = User::factory()->create();
        BlogPost::factory()->create([
            'slug'         => 'draft',
            'author_id'    => $author->id,
            'is_published' => false,
        ]);

        $this->get('/blog/draft')->assertStatus(404);
    }

    #[Test]
    public function legal_page_shows_content(): void
    {
        PlatformSetting::set('legal_terms', '<p>Nos CGU</p>');

        $this->get('/legal/terms')
             ->assertStatus(200)
             ->assertSee('Nos CGU');
    }

    #[Test]
    public function legal_page_returns_404_for_unknown(): void
    {
        $this->get('/legal/unknown')->assertStatus(404);
    }

    // ══════════════════════════════════════════════════════════
    // Admin — FAQ CRUD
    // ══════════════════════════════════════════════════════════

    #[Test]
    public function admin_can_list_faqs(): void
    {
        Faq::factory()->count(3)->create();

        $this->actingAs($this->makeAdmin())
             ->get('/admin/faqs')
             ->assertStatus(200)
             ->assertInertia(fn ($p) => $p->component('Admin/Faqs/Index')->has('faqs', 3));
    }

    #[Test]
    public function admin_can_create_faq(): void
    {
        $this->actingAs($this->makeAdmin())
             ->post('/admin/faqs', [
                 'question' => 'Comment démarrer ?',
                 'answer'   => 'Créez un compte gratuit.',
                 'category' => 'Général',
             ])->assertRedirect('/admin/faqs');

        $this->assertDatabaseHas('faqs', ['question' => 'Comment démarrer ?']);
    }

    #[Test]
    public function admin_can_update_faq(): void
    {
        $faq = Faq::factory()->create(['question' => 'Old']);

        $this->actingAs($this->makeAdmin())
             ->put("/admin/faqs/{$faq->id}", [
                 'question'  => 'New question',
                 'answer'    => 'New answer',
                 'is_active' => false,
             ])->assertRedirect('/admin/faqs');

        $this->assertDatabaseHas('faqs', ['id' => $faq->id, 'question' => 'New question']);
    }

    #[Test]
    public function admin_can_delete_faq(): void
    {
        $faq = Faq::factory()->create();

        $this->actingAs($this->makeAdmin())
             ->delete("/admin/faqs/{$faq->id}")
             ->assertRedirect();

        $this->assertDatabaseMissing('faqs', ['id' => $faq->id]);
    }

    // ══════════════════════════════════════════════════════════
    // Admin — Blog CRUD
    // ══════════════════════════════════════════════════════════

    #[Test]
    public function admin_can_list_blog_posts(): void
    {
        $author = User::factory()->create();
        BlogPost::factory()->count(2)->create(['author_id' => $author->id]);

        $this->actingAs($this->makeAdmin())
             ->get('/admin/blog-posts')
             ->assertStatus(200)
             ->assertInertia(fn ($p) => $p->component('Admin/BlogPosts/Index')->has('posts', 2));
    }

    #[Test]
    public function admin_can_create_blog_post(): void
    {
        $this->actingAs($this->makeAdmin())
             ->post('/admin/blog-posts', [
                 'title'        => 'Nouveau guide',
                 'body'         => 'Contenu du guide...',
                 'category'     => 'Guide',
                 'is_published' => true,
             ])->assertRedirect('/admin/blog-posts');

        $this->assertDatabaseHas('blog_posts', ['title' => 'Nouveau guide', 'is_published' => true]);
    }

    #[Test]
    public function admin_can_update_blog_post(): void
    {
        $admin = $this->makeAdmin();
        $post = BlogPost::factory()->create(['author_id' => $admin->id, 'title' => 'Old']);

        $this->actingAs($admin)
             ->put("/admin/blog-posts/{$post->id}", [
                 'title' => 'Updated title',
                 'body'  => 'Updated body',
             ])->assertRedirect('/admin/blog-posts');

        $this->assertDatabaseHas('blog_posts', ['id' => $post->id, 'title' => 'Updated title']);
    }

    #[Test]
    public function admin_can_delete_blog_post(): void
    {
        $admin = $this->makeAdmin();
        $post = BlogPost::factory()->create(['author_id' => $admin->id]);

        $this->actingAs($admin)
             ->delete("/admin/blog-posts/{$post->id}")
             ->assertRedirect();

        $this->assertDatabaseMissing('blog_posts', ['id' => $post->id]);
    }

    // ══════════════════════════════════════════════════════════
    // Admin — Contact Messages
    // ══════════════════════════════════════════════════════════

    #[Test]
    public function admin_can_list_contact_messages(): void
    {
        ContactMessage::factory()->count(3)->create();

        $this->actingAs($this->makeAdmin())
             ->get('/admin/contact-messages')
             ->assertStatus(200)
             ->assertInertia(fn ($p) => $p->component('Admin/ContactMessages/Index'));
    }

    #[Test]
    public function admin_can_view_and_mark_message_read(): void
    {
        $msg = ContactMessage::factory()->create(['is_read' => false]);

        $this->actingAs($this->makeAdmin())
             ->get("/admin/contact-messages/{$msg->id}")
             ->assertStatus(200)
             ->assertInertia(fn ($p) => $p->component('Admin/ContactMessages/Show'));

        $this->assertTrue($msg->fresh()->is_read);
    }

    #[Test]
    public function admin_can_delete_contact_message(): void
    {
        $msg = ContactMessage::factory()->create();

        $this->actingAs($this->makeAdmin())
             ->delete("/admin/contact-messages/{$msg->id}")
             ->assertRedirect();

        $this->assertDatabaseMissing('contact_messages', ['id' => $msg->id]);
    }

    // ══════════════════════════════════════════════════════════
    // Model logic
    // ══════════════════════════════════════════════════════════

    #[Test]
    public function blog_post_generates_unique_slug(): void
    {
        $author = User::factory()->create();
        BlogPost::factory()->create(['slug' => 'mon-titre', 'author_id' => $author->id]);

        $slug = BlogPost::generateSlug('Mon Titre');
        $this->assertEquals('mon-titre-1', $slug);
    }

    #[Test]
    public function faq_active_scope_filters_correctly(): void
    {
        Faq::factory()->create(['is_active' => true]);
        Faq::factory()->create(['is_active' => false]);

        $this->assertCount(1, Faq::active()->get());
    }
}
