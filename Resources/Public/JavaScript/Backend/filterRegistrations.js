// Pfad: vendor/jve/jv-events/Resources/Public/JavaScript/Backend/filterRegistrations.js
class FilterRegistrations {
    constructor() {
        this.form = document.getElementById('jveventFilterForm');
        if (!this.form) return;

        // Alle relevanten Filter-Elemente selektieren
        const filterElements = this.form.querySelectorAll('#jvevents, #recursive, #onlyActual');
        filterElements.forEach(element => {
            element.addEventListener('change', () => {
                this.form.submit();
            });
        });
    }
}
export default new FilterRegistrations();
