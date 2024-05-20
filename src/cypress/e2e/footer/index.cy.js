import { footer } from "../../commands/footer";

describe("Homepage", () => {
    beforeEach(() => {
        cy.visit("/");
    });

    it("testa a funcão footer", () => {
        footer();
    });
});