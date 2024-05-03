import { backFaqPage } from "../../commands/backFaqPage";

describe("faqPage", () => {
    beforeEach(() => {
        cy.visit("/");
        cy.contains("a", "Dúvidas frequentes").click();
    });

    it("Garante que as opções do menu 'Dúvidas Frequentes' sejam clicáveis", () => {
        const url = [
            "/perguntas-frequentes/cadastro/",
            "/perguntas-frequentes/IntroducaoMapa/",
            "/perguntas-frequentes/inscricao/"
        ];

        url.forEach(url => {
            const fullUrl = Cypress.config().baseUrl + url;

            cy.get(`.faq__frequent > [href="${fullUrl}"]`).click();
            backFaqPage();
            cy.wait(1000);
            cy.get(`.faq__links > [href="${fullUrl}"]`).click();
            backFaqPage();
        });
    });

    it("Garante que a barra de pesquisa funciona e carrega os resultados", () => {
        cy.get(".faq-search__input").should("be.enabled").type("cadastro");

        cy.get(".faq__main--results").should("be.visible");
        cy.get(".faq-accordion__items").should("be.visible");
    });
});