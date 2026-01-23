<?php

namespace App\Services\Chat\Handlers;

use App\Models\User;
use App\Services\Chat\IntentMatcher;
use App\Services\Chat\ResponseBuilder;

class FaqHandler
{
    private IntentMatcher $intentMatcher;

    public function __construct(IntentMatcher $intentMatcher)
    {
        $this->intentMatcher = $intentMatcher;
    }

    /**
     * Handle FAQ prompt - show FAQ list
     */
    public function handle(User $user): array
    {
        $faqs = config('chat.faqs', []);

        $builder = ResponseBuilder::make()
            ->text("Berikut adalah pertanyaan yang sering diajukan:")
            ->text("Pilih topik atau ketik pertanyaan Anda:");

        // Add FAQ options as quick replies
        foreach ($faqs as $key => $faq) {
            $builder->quickReply($faq['question'], 'faq_' . $key);
        }

        $builder->menuQuickReply();

        return $builder
            ->context((string) $user->id)
            ->build();
    }

    /**
     * Handle specific FAQ answer
     */
    public function handleAnswer(User $user, string $faqKey): array
    {
        $faqs = config('chat.faqs', []);

        if (!isset($faqs[$faqKey])) {
            return $this->handleNotFound($user);
        }

        $faq = $faqs[$faqKey];

        return ResponseBuilder::make()
            ->text("📌 *{$faq['question']}*")
            ->text($faq['answer'])
            ->quickReplies([
                ['title' => 'FAQ Lainnya', 'payload' => 'faq_prompt'],
                ['title' => 'Menu Utama', 'payload' => 'menu'],
            ])
            ->context((string) $user->id)
            ->build();
    }

    /**
     * Handle FAQ search from user message
     */
    public function handleSearch(User $user, string $message): array
    {
        $faqKey = $this->intentMatcher->matchFaqQuestion($message);

        if ($faqKey) {
            return $this->handleAnswer($user, $faqKey);
        }

        return $this->handleNotFound($user, $message);
    }

    /**
     * Handle when FAQ not found
     */
    private function handleNotFound(User $user, ?string $query = null): array
    {
        $text = $query
            ? "Maaf, saya tidak menemukan jawaban untuk \"{$query}\"."
            : "Maaf, FAQ tidak ditemukan.";

        return ResponseBuilder::make()
            ->text($text)
            ->text("Silakan pilih dari daftar FAQ atau hubungi customer service kami.")
            ->quickReplies([
                ['title' => 'Lihat FAQ', 'payload' => 'faq_prompt'],
                ['title' => 'Menu Utama', 'payload' => 'menu'],
            ])
            ->context((string) $user->id)
            ->build();
    }
}
