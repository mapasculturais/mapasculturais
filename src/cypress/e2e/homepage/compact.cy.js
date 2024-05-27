import { backHomepageCompact } from "../../commands/backHomepageCompact";

describe("Homepage compactada", () => {
    beforeEach(() => {
        cy.viewport(1000, 768);
        cy.visit("/");
    });

    it("Garante o funcionamento do home", () => {
        backHomepageCompact();
    });

    it("Acessa \"Ver todos\" de Eventos", () => {
        cy.get(":nth-child(2) > .card__right > .button").click();
        cy.url().should("include", "/eventos/");
        backHomepageCompact();
    });
    it("Acessa \"Ver todos\" de Espaços", () => {
        cy.get(":nth-child(3) > .card__right > .button").click();
        cy.url().should("include", "/espacos/");
        backHomepageCompact();
    });
    it("Acessa \"Ver todos\" de Agentes", () => {
        cy.get(":nth-child(4) > .card__right > .button").click();
        cy.url().should("include", "/agentes/");
        backHomepageCompact();
    });
    it("Acessa \"Ver todos\" de Projetos", () => {
        cy.get(":nth-child(5) > .card__right > .button").click();
        cy.url().should("include", "/projetos/");
        backHomepageCompact();
    });

    it("Navegação entre os cards da seção \"Em destaque\"", () => {
        cy.get(".carousel__next").click();
        cy.wait(1000);
        cy.get(".carousel__prev").click();
    });

    it("Acessa o navbar e o botão \"Acessar\" dos cards da seção \"Em destaque\"", () => {
        cy.get(".agents > a > span").click();
        cy.wait(1000);
        cy.get('.carousel__slide--next > .entity-card > .entity-card__footer > .entity-card__footer--action > .button').click();
        cy.url().should("include", "/agente/");
        backHomepageCompact();

        cy.get(".agents > a > span").click();
        cy.wait(1000);
        cy.get('.carousel__next').click();
        cy.get('.carousel__slide--next > .entity-card > .entity-card__footer > .entity-card__footer--action > .button').click();
        cy.url().should("include", "/agente/");
        backHomepageCompact();

        /*
        cy.get(".spaces > a > span").click();
        cy.wait(1000);
        cy.get("[style=\"width: 35.7143%; order: 0;\"] > .entity-card > .entity-card__footer > .entity-card__footer--action > .button").click();
        cy.url().should("include", "/espaco/25/#info");
        cy.contains("Quatro pixels");
        backHomepageCompact();
        */

        /*
        cy.get(".projects > a > span").click();
        cy.wait(1000);
        cy.get(".entity-card__footer--action > .button").click();
        cy.url().should("include", "/projeto/12/#info");
        cy.contains("12");
        backHomepageCompact();
        */
    });

    it("Acessa o botão \"Fazer Cadastro\" da quarta seção", () => {
        cy.get(".home-register__content--button").click();
        cy.url().should("include", "autenticacao/register/");
        cy.contains("Novo cadastro");
        backHomepageCompact();
    });

    it("Acessa o botões de zoom do mapa", () => {
        cy.get(".leaflet-control-zoom-in").click();
        cy.wait(2000);
        cy.get(".leaflet-control-zoom-out").click();
    });

    it("Acessa o botão \"Conheça o repositório\" da seção \"Alô desenvolvedores\"", () => {
        cy.get(".home-developers__content--link > .link").click();
        cy.url().should("include", "https://github.com/mapasculturais");
        cy.visit("/");
    });
});