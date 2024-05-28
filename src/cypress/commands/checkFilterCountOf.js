/* Checa a contagem de um elemento específico na página de filtros. */
function checkFilterCountOf(element) {
        cy.get('.foundResults').then(($foundResults) => {
                var resultsTextArray = $foundResults.text().split(" ");
                var resultsCount = Number(resultsTextArray[0]);

                switch (element) {
                        case "opportunity":
                                cy.get(".upper." + element + "__color").should("have.length", resultsCount);
                                cy.wait(1000);
                                cy.contains(resultsCount + " Oportunidades encontradas");
                                
                                break;
                        
                        case "project":
                                cy.get(".upper." + element + "__color").should("have.length", resultsCount);
                                cy.wait(1000);
                                cy.contains(resultsCount + " Projetos encontrados");
        
                                break;
                        
                        case "space":
                                cy.get(".upper." + element + "__color").should("have.length", resultsCount);
                                cy.wait(1000);
                                cy.contains(resultsCount + " Espaços encontrados");
                
                                break;
                        
                        default:
                                cy.log("[-] Tipo inválido, use \"opportunity\", \"space\" ou \"project\"");
                                cy.contains("FORCE ERROR");
                                
                                break;
                }
        });
}

module.exports = { checkFilterCountOf };