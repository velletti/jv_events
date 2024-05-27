class ShowEventInFrontend {
    constructor() {
        this.clickableButtons = document.querySelectorAll(".windowOpenUri");
        this.initializeClickableButtons();
    }

    initializeClickableButtons() {
        this.clickableButtons.forEach(button => {
            button.addEventListener('click', function (evt) {
                evt.preventDefault();
                window.open(button.getAttribute('data-uri'), '_blank');
            });
        });
    }

}
export default new ShowEventInFrontend;