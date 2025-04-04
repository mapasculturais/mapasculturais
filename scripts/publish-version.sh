## Script para gerar nova versão na branch de produção a partir de uma branch estável ##

exibir_uso_correto() {
    echo "Usar: $0 <versao> [versao_producao] [versao_candidata]"
    echo "Onde:"
    echo "  <versao_producao> indentificador da versão a publicar."
    echo "     Exemplo:"
    echo "       $0 v7.6.0-minc14 => gerará versão identificada como v7.6.0-minc14"
    echo "  <versao_candidata> indentificador da versão candidata a próxima publicação."
    echo "     Exemplo:"
    echo "       $0 v7.6.0-minc15-RC => gerará versão identificada como v7.6.0-minc15-RC"
}

# Verificações
ENV_ERROR=0
if [[ -z "$COMMIT_MSG_NEW_VERSION" ]]; then
    (( ENV_ERROR++ ))
    echo "#ENV_ERROR_$ENV_ERROR"
    echo "  Este script requer a variável de ambiente \$COMMIT_MSG_NEW_VERSION definida."
    echo "  Essa variável deve conter a mensagem dos commits de atualização de versão."
    echo "  Exemplo: 'Atualiza identificador de versão'"
fi
if [ -e "$STABLE_BRANCH/." ]; then
    (( ENV_ERROR++ ))
    echo "#ENV_ERROR_$ENV_ERROR"
    echo "  Este script requer a variável de ambiente \$STABLE_BRANCH definida." 
    echo "  Essa variável deve conter o nome da branch estável que alimenta a branch de produção."
    echo "  Exemplo: 'develop'"
fi
if [ -e "$PROD_BRANCH/." ]; then
    (( ENV_ERROR++ ))
    echo "#ENV_ERROR_$ENV_ERROR"
    echo "  Este script requer a variável de ambiente \$PROD_BRANCH definida." 
    echo "  Essa variável deve conter o nome da branch de produção."
    echo "  Exemplo: 'master'"
fi
if [ $ENV_ERROR -gt 0 ]; then
    exit 1
fi
if [[ $# -ne 2 ]]; then
    echo "Erro: quantidade parâmetros incorreta."
    exibir_uso_correto
    exit 1
fi

git rev-parse --git-dir > /dev/null 2>&1;
if [[ $? -ne 0 || ! -f "version.txt" ]];
then
    echo "Este script precisa ser executado na raiz do repositório."
    echo "Pois é lá que está o arquivo 'version.txt' que precisa ser atualizado."
    exit 1
fi

# Início das operações

# Posiciona-se no commit mais recente da branch estável

git switch $STABLE_BRANCH
git pull

# Efetua ajustes da nova versão

git checkout -b release/$1
echo $1 > version.txt
git add version.txt
git commit -m "$COMMIT_MSG_NEW_VERSION"

# Incorpora a branch estável à branch de produção

git switch $PROD_BRANCH
git pull
git merge --no-ff --no-edit -Xtheirs release/$1
git push
git tag $1
git push origin $1

# Atualizar elementos relativos ao próximo release

git switch $STABLE_BRANCH
git pull
echo $2 > version.txt
git add version.txt
git commit -m "$COMMIT_MSG_NEW_VERSION"
git push

