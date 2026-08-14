import Link from 'next/link';

const STEPS = [
  { key: 'cart', label: 'Cart', href: '/cart' },
  { key: 'address', label: 'Address', href: '/checkout' },
  { key: 'payment', label: 'Payment', href: '/checkout' },
] as const;

type StepKey = (typeof STEPS)[number]['key'];

function stepIndex(key: StepKey) {
  return STEPS.findIndex((step) => step.key === key);
}

export function CheckoutProgress({ current }: { current: StepKey }) {
  const activeIndex = stepIndex(current);

  return (
    <nav className="qc-progress" aria-label="Checkout progress">
      {STEPS.flatMap((step, index) => {
        const state =
          index < activeIndex ? 'is-done' : index === activeIndex ? 'is-active' : '';
        const content = (
          <>
            <span className="qc-progress__num">{index < activeIndex ? '✓' : index + 1}</span>
            {step.label}
          </>
        );

        const node =
          index < activeIndex ? (
            <Link key={step.key} href={step.href} className={`qc-progress__step ${state}`}>
              {content}
            </Link>
          ) : (
            <span key={step.key} className={`qc-progress__step ${state}`}>
              {content}
            </span>
          );

        if (index === 0) return [node];
        return [
          <span key={`${step.key}-sep`} className="qc-progress__sep" aria-hidden="true" />,
          node,
        ];
      })}
    </nav>
  );
}
