/* Checa a contagem de algum elemento específico na página de filtros. O segundo argumento é para saber se a verificação ocorre após uma limpeza de filtros ou não */
function checkFilterCountOf(element, clear) {
        switch (clear) {
                case false:
                        switch (element) {
                                case "opportunity":
                                        cy.get('.foundResults').then(($foundResults) => {
                                                let resultsTextArray = $foundResults.text().split(" ");
                                                let resultsCount = Number(resultsTextArray[0]);
                                        
                                                cy.get(".upper." + element + "__color").should("have.length", resultsCount);
                                                cy.wait(1000);
                                                cy.contains(resultsCount + " Oportunidades encontradas");
                                        });
                                
                                        break;
                        
                                case "project":
                                        cy.get('.foundResults').then(($foundResults) => {
                                                let resultsTextArray = $foundResults.text().split(" ");
                                                let resultsCount = Number(resultsTextArray[0]);
                                        
                                                cy.get(".upper." + element + "__color").should("have.length", resultsCount);
                                                cy.wait(1000);
                                                cy.contains(resultsCount + " Projetos encontrados");
                                        });
        
                                        break;
                        
                                case "space":
                                        cy.get('.foundResults').then(($foundResults) => {
                                                let resultsTextArray = $foundResults.text().split(" ");
                                                let resultsCount = Number(resultsTextArray[0]);
                                                
                                                cy.get(".upper." + element + "__color").should("have.length", resultsCount);
                                                cy.wait(1000);
                                                cy.contains(resultsCount + " Espaços encontrados");
                                        });
                
                                        break;
                        
                                default:
                                        cy.log("[-] Tipo inválido, use \"opportunity\", \"space\" ou \"project\"");
                                        cy.contains("FORCE ERROR");
                                
                                        break;
                        }

                        break;
                case true:
                        let countBeforeClear;

                        switch (element) {
                                case "opportunity":
                                        cy.get('.foundResults').then(($foundResults) => {
                                                let resultsTextArray = $foundResults.text().split(" ");
                                                countBeforeClear = Number(resultsTextArray[0]);
                                        });

                                        cy.get('.foundResults').then(($foundResults) => {
                                                let resultsTextArray = $foundResults.text().split(" ");
                                                let resultsCount = Number(resultsTextArray[0]);
            
                                                cy.get(".upper." + element + "__color").should("have.length", resultsCount);
                                                cy.wait(1000);
                                                cy.get(".upper." + element + "__color").should("have.length", countBeforeClear);
                                                cy.wait(1000);
                                                cy.contains(resultsCount + " Oportunidades encontradas");
                                        });

                                        break;

                                case "space":
                                        cy.get('.foundResults').then(($foundResults) => {
                                                let resultsTextArray = $foundResults.text().split(" ");
                                                countBeforeClear = Number(resultsTextArray[0]);
                                        });

                                        cy.get('.foundResults').then(($foundResults) => {
                                                let resultsTextArray = $foundResults.text().split(" ");
                                                let resultsCount = Number(resultsTextArray[0]);
            
                                                cy.get(".upper." + element + "__color").should("have.length", resultsCount);
                                                cy.wait(1000);
                                                cy.get(".upper." + element + "__color").should("have.length", countBeforeClear);
                                                cy.wait(1000);
                                                cy.contains(resultsCount + " Espaços encontrados");
                                        });

                                        break;

                                case "project":
                                        cy.get('.foundResults').then(($foundResults) => {
                                                let resultsTextArray = $foundResults.text().split(" ");
                                                countBeforeClear = Number(resultsTextArray[0]);
                                        });

                                        cy.get('.foundResults').then(($foundResults) => {
                                                let resultsTextArray = $foundResults.text().split(" ");
                                                let resultsCount = Number(resultsTextArray[0]);

                                                cy.get(".upper." + element + "__color").should("have.length", resultsCount);
                                                cy.wait(1000);
                                                cy.get(".upper." + element + "__color").should("have.length", countBeforeClear);
                                                cy.wait(1000);
                                                cy.contains(resultsCount + " Projetos encontrados");
                                        });
                                
                                        break;

                                default:
                                        cy.log("[-] Tipo inválido, use \"opportunity\", \"space\" ou \"project\"");
                                        cy.contains("FORCE ERROR");

                                        break;
                        }

                        break;
                default:
                        cy.log("[-] Defina o segundo argumento com true ou false. True será usado quando a checagem é para ser feita em uma limpeza de filtros, e false caso contrário");
                        cy.contains("FORCE ERROR");

                        break;
        }
}

module.exports = { checkFilterCountOf };