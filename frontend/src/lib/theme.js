// Each survey gets a consistent accent color derived from its id, the way
// Microsoft Forms assigns a cover color per form. Six curated, vibrant-but-
// professional hues — not a full rainbow, so it stays coherent as a set.
const ACCENTS = [
  { name: 'indigo', solid: '#4F46E5', soft: '#EEF0FF', text: '#3730A3' },
  { name: 'coral', solid: '#FF6B4A', soft: '#FFEEE8', text: '#C2410C' },
  { name: 'teal', solid: '#0D9488', soft: '#E6FBF8', text: '#0F766E' },
  { name: 'amber', solid: '#D97706', soft: '#FFF6E5', text: '#B45309' },
  { name: 'rose', solid: '#E11D48', soft: '#FFEBEF', text: '#BE123C' },
  { name: 'violet', solid: '#7C3AED', soft: '#F4EEFF', text: '#6D28D9' },
];

// Sampled directly from the Mayshowa logo — navy from the wordmark, teal from
// the "Working Together" script. Swap MAYSHOWA_BRAND's solid/soft/text to the
// teal set below if you'd rather use that one instead.
const MAYSHOWA_BRAND = { name: 'mayshowa-navy', solid: '#1E3C64', soft: '#E7ECF3', text: '#1E3C64' };
// const MAYSHOWA_BRAND = { name: 'mayshowa-teal', solid: '#2F8B90', soft: '#E7F4F4', text: '#1F6367' };

const BRAND_OVERRIDES = {
  'mayshowa-employee-engagement-survey-2026': MAYSHOWA_BRAND,
};

// Pass either just an id (rotates through the palette as before), or a full
// survey object (checked against BRAND_OVERRIDES by slug first).
export function accentFor(idOrSurvey) {
  if (idOrSurvey && typeof idOrSurvey === 'object') {
    if (idOrSurvey.slug && BRAND_OVERRIDES[idOrSurvey.slug]) {
      return BRAND_OVERRIDES[idOrSurvey.slug];
    }
    return accentFor(idOrSurvey.id);
  }
  const n = typeof idOrSurvey === 'number' ? idOrSurvey : parseInt(idOrSurvey, 10) || 0;
  return ACCENTS[n % ACCENTS.length];
}