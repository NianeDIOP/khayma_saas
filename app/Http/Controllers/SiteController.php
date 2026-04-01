<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\ContactMessage;
use App\Models\Faq;
use App\Models\Module;
use App\Models\Plan;
use App\Models\PlatformSetting;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function modules()
    {
        $modules = Module::active()->orderBy('name')->get();

        return view('site.modules', compact('modules'));
    }

    public function pricing()
    {
        $plans = Plan::active()->orderBy('price_monthly')->get();

        return view('site.pricing', compact('plans'));
    }

    public function demo()
    {
        return view('site.demo');
    }

    public function contact()
    {
        return view('site.contact');
    }

    public function contactStore(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'phone'   => 'nullable|string|max:30',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        ContactMessage::create($validated);

        return back()->with('success', 'Votre message a bien été envoyé. Nous vous répondrons rapidement.');
    }

    public function faq()
    {
        $faqs = Faq::active()->orderBy('sort_order')->orderBy('id')->get();
        $categories = $faqs->pluck('category')->filter()->unique()->values();

        return view('site.faq', compact('faqs', 'categories'));
    }

    public function blog()
    {
        $posts = BlogPost::published()
            ->with('author:id,name')
            ->orderByDesc('published_at')
            ->paginate(12);

        return view('site.blog', compact('posts'));
    }

    public function blogShow(BlogPost $blogPost)
    {
        if (! $blogPost->is_published) {
            abort(404);
        }

        return view('site.blog-show', compact('blogPost'));
    }

    public function legal(string $page)
    {
        $allowed = ['privacy', 'terms', 'sales', 'mentions', 'cookies'];
        if (! in_array($page, $allowed)) {
            abort(404);
        }

        $titles = [
            'privacy'  => 'Politique de confidentialité',
            'terms'    => 'Conditions Générales d\'Utilisation',
            'sales'    => 'Conditions Générales de Vente',
            'mentions' => 'Mentions légales',
            'cookies'  => 'Politique de cookies',
        ];

        $content = PlatformSetting::get('legal_' . $page, '');

        return view('site.legal', [
            'title'   => $titles[$page],
            'content' => $content,
        ]);
    }
}
