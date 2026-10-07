<!-- Preconnect Google Fonts for Velara FAQ -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    .faq-velara-easing {
        transition-timing-function: cubic-bezier(0.22, 1, 0.36, 1);
    }
</style>

@php
    $faqItems = [
        [
            'q' => 'What are your operating hours?',
            'a' => 'We operate Monday through Friday, 9 AM to 6 PM (EST). Our online store is available 24/7, and customer support responds within 24 hours on weekdays.'
        ],
        [
            'q' => 'How does your delivery service work?',
            'a' => 'We partner with leading courier services to ensure fast and reliable delivery. Standard shipping takes 3-5 business days, while express delivery is available for 1-2 business day turnaround. Free shipping on orders over $50.'
        ],
        [
            'q' => "What's covered by your return policy?",
            'a' => 'We offer a 30-day hassle-free return policy on all products. Items must be in their original condition and packaging. Simply contact our support team to initiate a return and receive a prepaid shipping label.'
        ],
        [
            'q' => 'Which payment methods do you accept?',
            'a' => 'We accept all major credit and debit cards (Visa, Mastercard, Amex), PayPal, Apple Pay, Google Pay, and bank transfers. All transactions are secured with 256-bit SSL encryption.'
        ],
        [
            'q' => 'How does the rewards program benefit me?',
            'a' => 'Our rewards program lets you earn points on every purchase. Accumulate 100 points to unlock a $5 discount. Members also get early access to sales, exclusive deals, and birthday bonuses.'
        ],
    ];
@endphp

<!-- FAQ 02 Velara Section -->
<section class="bg-black py-[96px] px-6 md:px-[60px] relative overflow-hidden select-none max-w-full"
         style="font-family: 'Inter', sans-serif;"
         x-data="{ openIndex: null }">

    <!-- Ambient Glow Layer -->
    <div class="absolute top-0 left-0 w-full h-full pointer-events-none overflow-hidden">
        <div class="absolute top-1/2 left-0 -translate-y-1/2 w-[500px] h-[500px] bg-[#A855F7]/5 blur-[120px] rounded-full max-w-full"></div>
    </div>

    <!-- Content Wrapper -->
    <div class="max-w-[1450px] mx-auto relative z-10 w-full">

        <!-- Header -->
        <div class="mb-[64px] flex flex-col items-center text-center">
            <!-- Eyebrow row -->
            <div class="flex items-center gap-[10px] mb-3">
                <span class="text-[14px] text-[#555555] font-medium">07.</span>
                <span class="text-[#333333]">—</span>
                <span class="text-[13px] text-[#555555] font-medium tracking-[2px] uppercase">Questions & Support</span>
            </div>

            <!-- H2 Headline -->
            <h2 class="text-[36px] md:text-[52px] font-medium text-white leading-[1.1] max-w-[820px] mb-4 tracking-tight">
                Frequently Asked Questions
            </h2>

            <!-- Sub-paragraph -->
            <p class="text-[16px] text-white/40 font-medium max-w-[600px]">
                Find answers to common questions about our platform and financial management tools.
            </p>
        </div>

        <!-- Accordion List -->
        <div class="grid grid-cols-1 gap-4 max-w-[1000px] w-full mx-auto">
            @foreach($faqItems as $idx => $item)
                <!-- Item Wrapper -->
                <div class="relative overflow-hidden rounded-[16px] border transition-all duration-500"
                     :class="openIndex === {{ $idx }} 
                        ? 'bg-white/[0.05] border-[#A855F7]/30 shadow-[0_0_30px_rgba(168,85,247,0.1)]' 
                        : 'bg-white/[0.02] border-white/5 hover:border-white/10 hover:bg-white/[0.03]'">

                    <!-- Brand Accent Bar (left edge 3px) -->
                    <div class="absolute left-0 top-0 bottom-0 w-[3px] transition-all duration-500 bg-[#A855F7] origin-center pointer-events-none"
                         :class="openIndex === {{ $idx }} ? 'opacity-100 scale-y-100' : 'opacity-40 scale-y-75'"></div>

                    <!-- Question Row -->
                    <div @click="openIndex = (openIndex === {{ $idx }} ? null : {{ $idx }})"
                         class="flex items-center justify-between py-[24px] pr-[28px] pl-[32px] cursor-pointer">
                        
                        <h3 class="text-[16px] md:text-[18px] font-medium transition-colors duration-300"
                            :class="openIndex === {{ $idx }} ? 'text-white' : 'text-white/80'">
                            {{ $item['q'] }}
                        </h3>

                        <!-- Rotating Plus/Cross SVG -->
                        <div class="flex-shrink-0 ml-4">
                            <svg class="w-6 h-6 transition-transform duration-400 faq-velara-easing"
                                 :class="openIndex === {{ $idx }} ? 'rotate-45 text-[#A855F7]' : 'rotate-0 text-white/40'"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="2"
                                 stroke-linecap="round">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                        </div>
                    </div>

                    <!-- Smooth Expanding Answer Panel -->
                    <div class="grid transition-all duration-400 faq-velara-easing"
                         :class="openIndex === {{ $idx }} ? 'grid-rows-[1fr] opacity-100' : 'grid-rows-[0fr] opacity-0'">
                        <div class="overflow-hidden">
                            <div class="px-[32px] pb-[28px]">
                                <p class="text-[15px] text-white/50 leading-[1.8] font-medium max-w-[90%]">
                                    {{ $item['a'] }}
                                </p>
                            </div>
                        </div>
                    </div>

                </div>
            @endforeach
        </div>

    </div>
</section>
