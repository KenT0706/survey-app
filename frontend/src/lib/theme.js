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

export function accentFor(id) {
  const n = typeof id === 'number' ? id : parseInt(id, 10) || 0;
  return ACCENTS[n % ACCENTS.length];
}