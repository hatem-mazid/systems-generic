export default function isRTL() {
    return document.documentElement.getAttribute('dir') === 'rtl';
}
