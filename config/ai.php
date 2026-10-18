<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Global AI Yordamchi O'chirgichi (Master Switch)
    |--------------------------------------------------------------------------
    | Agar false bo'lsa, barcha kitoblar ichidagi AI yordamchi o'chiriladi.
    */
    'enabled' => env('AI_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Asosiy LLM va Embedding Provayderlari
    |--------------------------------------------------------------------------
    | Qo'llab-quvvatlanadigan LLM provayderlar: "openai", "anthropic"
    | Qo'llab-quvvatlanadigan Embedding provayderlar: "openai", "none" (faqat FULLTEXT)
    |
    | Eslatma: Model nomlari kodga qotirilmagan. Rasmiy hujjatlar bo'yicha
    | joriy tavsiya etilgan modellar:
    | - OpenAI Chat: "gpt-4o-mini" yoki "gpt-4o" (gpt-3.5-turbo eskirgan, ishlatilmaydi)
    | - OpenAI Embedding: "text-embedding-3-small" (1536 o'lchamli, float32)
    | - Anthropic Chat: "claude-3-5-haiku-latest" yoki "claude-sonnet-4-20250514"
    */
    'provider' => env('AI_PROVIDER', 'gemini'),

    'embedding_provider' => env('AI_EMBEDDING_PROVIDER', 'gemini'),

    'providers' => [
        'openai' => [
            'api_key'         => env('OPENAI_API_KEY'),
            'base_url'        => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
            'chat_model'      => env('OPENAI_CHAT_MODEL', 'gpt-4o-mini'),
            'embedding_model' => env('OPENAI_EMBEDDING_MODEL', 'text-embedding-3-small'),
            'timeout'         => (int) env('OPENAI_TIMEOUT', 30),
        ],

        'anthropic' => [
            'api_key'           => env('ANTHROPIC_API_KEY'),
            'base_url'          => env('ANTHROPIC_BASE_URL', 'https://api.anthropic.com/v1'),
            'chat_model'        => env('ANTHROPIC_CHAT_MODEL', 'claude-3-5-haiku-latest'),
            'anthropic_version' => env('ANTHROPIC_VERSION', '2023-06-01'),
            'timeout'           => (int) env('ANTHROPIC_TIMEOUT', 30),
        ],

        'gemini' => [
            'api_key'         => env('GEMINI_API_KEY'),
            'base_url'        => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
            'chat_model'      => env('GEMINI_CHAT_MODEL', 'gemini-flash-lite-latest'),
            'embedding_model' => env('GEMINI_EMBEDDING_MODEL', 'gemini-embedding-2'),
            'timeout'         => (int) env('GEMINI_TIMEOUT', 60),
        ],

        'groq' => [
            'api_key'    => env('GROQ_API_KEY'),
            'base_url'   => env('GROQ_BASE_URL', 'https://api.groq.com/openai/v1'),
            'chat_model' => env('GROQ_CHAT_MODEL', 'llama-3.3-70b-versatile'),
            'timeout'    => (int) env('GROQ_TIMEOUT', 30),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cheklovlar va Xavfsizlik Parametrlari
    |--------------------------------------------------------------------------
    */
    'daily_limit_per_user' => (int) env('AI_DAILY_LIMIT_PER_USER', 20),
    'minute_throttle'      => (int) env('AI_MINUTE_THROTTLE', 6),
    'max_question_chars'   => (int) env('AI_MAX_QUESTION_CHARS', 500),
    'max_context_chunks'   => (int) env('AI_MAX_CONTEXT_CHUNKS', 6),
    'max_output_tokens'    => (int) env('AI_MAX_OUTPUT_TOKENS', 700),
    'history_turns'        => (int) env('AI_HISTORY_TURNS', 6),

    /*
    |--------------------------------------------------------------------------
    | Qidiruv (Retriever) va RelevanceGate Kalibrlash Chegaralari
    |--------------------------------------------------------------------------
    | AI_MIN_SIMILARITY: 10 ta kitobga oid va 10 ta begona savolda o'lchab
    | tanlangan gibrid (vektor + leksik) o'xshashlik chegarasi (0.22).
    | AI_MIN_FULLTEXT_SCORE: Embedding kaliti bo'lmaganda faqat FULLTEXT/leksik
    | rejim uchun minimal chegara (0.12).
    */
    'min_similarity'     => (float) env('AI_MIN_SIMILARITY', 0.22),
    'min_fulltext_score' => (float) env('AI_MIN_FULLTEXT_SCORE', 0.12),

    /*
    |--------------------------------------------------------------------------
    | PDF Matn Sifati va Bo'laklash (Chunking) Sozlamalari
    |--------------------------------------------------------------------------
    */
    'min_text_quality'       => (float) env('AI_MIN_TEXT_QUALITY', 0.45),
    'min_avg_chars_per_page' => (int) env('AI_MIN_AVG_CHARS_PER_PAGE', 120),
    'chunk_min_words'        => (int) env('AI_CHUNK_MIN_WORDS', 250),
    'chunk_max_words'        => (int) env('AI_CHUNK_MAX_WORDS', 400),
    'chunk_overlap_ratio'    => (float) env('AI_CHUNK_OVERLAP_RATIO', 0.15),
    'index_batch_size'       => (int) env('AI_INDEX_BATCH_SIZE', 15),
    'pdftotext_bin'          => env('PDFTOTEXT_BIN', 'pdftotext'),

    /*
    |--------------------------------------------------------------------------
    | OCR (Optical Character Recognition) Skanerlangan PDF Sozlamalari
    |--------------------------------------------------------------------------
    */
    'ocr' => [
        'enabled'         => env('AI_OCR_ENABLED', true),
        'engine'          => env('AI_OCR_ENGINE', 'auto'), // auto, python, tesseract
        'uv_bin'          => env('UV_BIN', 'uv'),
        'python_bin'      => env('PYTHON_BIN', 'python'),
        'tesseract_bin'   => env('TESSERACT_BIN', 'tesseract'),
        'pdftoppm_bin'    => env('PDFTOPPM_BIN', 'pdftoppm'),
        'languages'       => env('AI_OCR_LANGUAGES', 'uzb+rus+eng'),
        'dpi'             => (int) env('AI_OCR_DPI', 150),
        'timeout_seconds' => (int) env('AI_OCR_TIMEOUT_SECONDS', 600),
        'max_pages'       => (int) env('AI_OCR_MAX_PAGES', 50),
    ],

    /*
    |--------------------------------------------------------------------------
    | Navbat (Queue) va Katta Kitoblarni Fon Rejimida Indekslash
    |--------------------------------------------------------------------------
    */
    'queue' => [
        'enabled'      => env('AI_QUEUE_INDEXING', false),
        'connection'   => env('AI_QUEUE_CONNECTION', env('QUEUE_CONNECTION', 'sync')),
        'queue_name'   => env('AI_QUEUE_NAME', 'default'),
        'lock_seconds' => (int) env('AI_INDEX_LOCK_SECONDS', 900),
    ],

];
