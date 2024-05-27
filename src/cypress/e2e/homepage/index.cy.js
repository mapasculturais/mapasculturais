describe("Homepage", () => {
    beforeEach(() => {
        cy.visit("/");
    });

    it("Garante que a home page funciona", () => {
        cy.contains("label", "Boas vindas ao Mapa Cultural");
    });
});