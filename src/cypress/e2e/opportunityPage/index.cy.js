describe("Oportunidade", () => {
    it("Garante que o oportunidades seja clicável, permite digitar no campo de busca e navega para uma URL específica", () => {
        cy.visit("/");
        cy.wait(1000);
        cy.get(".mc-header-menu__btn-mobile").click();
        cy.contains(".mc-header-menu__itens a", "Oportunidades").click();
        cy.url().should("include", "/oportunidades");
        cy.get(".search-filter__actions--form-input").type("DJs");
        cy.wait(1000);
        cy.visit("/oportunidade/78/#info");
        cy.wait(1000);
        cy.url().should("include", "/oportunidade/");
    });

    it("Garante que os cards de indicadores das oportunidades funciona", () => {
        cy.visit("/oportunidades");
        cy.get(':nth-child(1) > .entity-cards-cards__content > .entity-cards-cards__info > .entity-cards-cards__label').should('have.text', 'Oportunidades criadas');
        cy.get(':nth-child(2) > .entity-cards-cards__content > .entity-cards-cards__info > .entity-cards-cards__label').should('have.text', 'Oportunidades certificadas');

        cy.wait(1000);

        cy.get(".foundResults").invoke('text').then((text) => {
            let expectedCount = Number(text.match(/\d+/), 10);
            console.log(expectedCount);
            cy.get('#main-app > div.search > div.entity-cards > div > div > div:nth-child(1) > div > div.entity-cards-cards__info > strong').should('have.text', expectedCount);
        });

    });

    it("Garante que a tab dashboard de oportunidades funciona", () => {
        cy.visit("/oportunidades");
        cy.get('.indicator > a > span').should('have.text', 'Indicadores');
        cy.get('.indicator > a').click();

        cy.wait(1000);

        cy.get('#iFrameResizer0').should('be.visible');
    });
});