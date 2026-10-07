import React, { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';

const faqData = [
  {
    q: 'What are your operating hours?',
    a: 'We operate Monday through Friday, 9 AM to 6 PM (EST). Our online store is available 24/7, and customer support responds within 24 hours on weekdays.',
  },
  {
    q: 'How does your delivery service work?',
    a: 'We partner with leading courier services to ensure fast and reliable delivery. Standard shipping takes 3-5 business days, while express delivery is available for 1-2 business day turnaround. Free shipping on orders over $50.',
  },
  {
    q: "What's covered by your return policy?",
    a: 'We offer a 30-day hassle-free return policy on all products. Items must be in their original condition and packaging. Simply contact our support team to initiate a return and receive a prepaid shipping label.',
  },
  {
    q: 'Which payment methods do you accept?',
    a: 'We accept all major credit and debit cards (Visa, Mastercard, Amex), PayPal, Apple Pay, Google Pay, and bank transfers. All transactions are secured with 256-bit SSL encryption.',
  },
  {
    q: 'How does the rewards program benefit me?',
    a: 'Our rewards program lets you earn points on every purchase. Accumulate 100 points to unlock a $5 discount. Members also get early access to sales, exclusive deals, and birthday bonuses.',
  },
];

export default function Faq02Velara() {
  const [openIndex, setOpenIndex] = useState(null);

  const toggleItem = (index) => {
    setOpenIndex((prev) => (prev === index ? null : index));
  };

  return (
    <>
      {/* Inline Google Fonts Preconnect & Import scoped to this section */}
      <link rel="preconnect" href="https://fonts.googleapis.com" />
      <link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="anonymous" />
      <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet"
      />

      <section
        className="bg-black py-[96px] px-6 md:px-[60px] relative overflow-hidden font-sans select-none"
        style={{ fontFamily: "'Inter', sans-serif" }}
      >
        {/* Ambient glow layer */}
        <div className="absolute top-0 left-0 w-full h-full pointer-events-none">
          <div className="absolute top-1/2 left-0 -translate-y-1/2 w-[500px] h-[500px] bg-[#A855F7]/5 blur-[120px] rounded-full" />
        </div>

        {/* Content wrapper */}
        <div className="max-w-[1450px] mx-auto relative z-10">
          {/* Header */}
          <div className="mb-[64px] flex flex-col items-center text-center">
            {/* Eyebrow row */}
            <div className="flex items-center gap-[10px] mb-3">
              <span className="text-[14px] text-[#555555] font-medium">07.</span>
              <span className="text-[#333333]">—</span>
              <span className="text-[13px] text-[#555555] font-medium tracking-[2px] uppercase">
                Questions & Support
              </span>
            </div>

            {/* H2 Headline */}
            <h2 className="text-[36px] md:text-[52px] font-medium text-white leading-[1.1] max-w-[820px] mb-4 tracking-tight">
              Frequently Asked Questions
            </h2>

            {/* Sub-paragraph */}
            <p className="text-[16px] text-white/40 font-medium max-w-[600px]">
              Find answers to common questions about our platform and financial management tools.
            </p>
          </div>

          {/* Accordion list */}
          <div className="grid grid-cols-1 gap-4 max-w-[1000px] mx-auto">
            {faqData.map((item, index) => {
              const isOpen = openIndex === index;

              return (
                <div
                  key={index}
                  className={`relative overflow-hidden rounded-[16px] border transition-all duration-500 ${
                    isOpen
                      ? 'bg-white/[0.05] border-[#A855F7]/30 shadow-[0_0_30px_rgba(168,85,247,0.1)]'
                      : 'bg-white/[0.02] border-white/5 hover:border-white/10 hover:bg-white/[0.03]'
                  }`}
                >
                  {/* Brand accent bar (left edge) */}
                  <div
                    className={`absolute left-0 top-0 bottom-0 w-[3px] transition-all duration-500 bg-[#A855F7] origin-center pointer-events-none ${
                      isOpen ? 'opacity-100 scale-y-100' : 'opacity-40 scale-y-75'
                    }`}
                  />

                  {/* Question row */}
                  <div
                    onClick={() => toggleItem(index)}
                    className="flex items-center justify-between py-[24px] pr-[28px] pl-[32px] cursor-pointer"
                  >
                    <h3
                      className={`text-[16px] md:text-[18px] font-medium transition-colors duration-300 ${
                        isOpen ? 'text-white' : 'text-white/80'
                      }`}
                    >
                      {item.q}
                    </h3>

                    {/* Icon wrapper with rotating plus */}
                    <div className="flex-shrink-0 ml-4">
                      <motion.div
                        animate={{ rotate: isOpen ? 45 : 0 }}
                        transition={{ duration: 0.4, ease: [0.22, 1, 0.36, 1] }}
                      >
                        <svg
                          width="24"
                          height="24"
                          viewBox="0 0 24 24"
                          fill="none"
                          stroke={isOpen ? '#A855F7' : 'currentColor'}
                          strokeWidth={2}
                          strokeLinecap="round"
                          className={isOpen ? '' : 'text-white/40'}
                        >
                          <line x1="12" y1="5" x2="12" y2="19" />
                          <line x1="5" y1="12" x2="19" y2="12" />
                        </svg>
                      </motion.div>
                    </div>
                  </div>

                  {/* Answer panel */}
                  <AnimatePresence initial={false}>
                    {isOpen && (
                      <motion.div
                        initial={{ height: 0, opacity: 0 }}
                        animate={{ height: 'auto', opacity: 1 }}
                        exit={{ height: 0, opacity: 0 }}
                        transition={{ duration: 0.4, ease: [0.22, 1, 0.36, 1] }}
                        className="overflow-hidden"
                      >
                        <div className="px-[32px] pb-[28px]">
                          <p className="text-[15px] text-white/50 leading-[1.8] font-medium max-w-[90%]">
                            {item.a}
                          </p>
                        </div>
                      </motion.div>
                    )}
                  </AnimatePresence>
                </div>
              );
            })}
          </div>
        </div>
      </section>
    </>
  );
}
