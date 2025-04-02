## Script para deploy de uma referëncia qualquer (branch, tag ou PR) ##

exibir_uso_correto() {
    echo "Usar: $0 <versao> [opcao]"
    echo "Onde:"
    echo "  <versao> indica a branch, a tag ou o PR a publicar."
    echo "     no caso de branch ou tag, escreva o nome dela;"
    echo "     no caso de PR, escreva pull/<numero_do_pr>"
    echo "     Exemplos:"
    echo "       $0 v7.6.0-minc14 => publicará a tag v7.6.0-minc14"
    echo "       $0 $STABLE_BRANCH => publicará a branch $STABLE_BRANCH"
    echo "       $0 pull/39 => publicará o PR#39"
    echo "  [opcao] indica o que deve ser feito após a preparação do diretório"
    echo "     se omitida, fará build da versão desejada, seguirá"
    echo "       realizando o build, derrubando a versão corrente, e concluirá"
    echo "       subindo a nova versão."
    echo "     --build-only fará somente o build, sem mexer na instância em execução"
    echo "     --clone-only fará somente a preparação do diretório e nem faz o build"
}
configurar_detalhes_minc() {
    mkdir $DEPLOY_DIR/docker-data
    ln -s $PRIVATE_FILES_PATH $DEPLOY_DIR/docker-data/private-files
    ln -s $PUBLIC_FILES_PATH $DEPLOY_DIR/docker-data/public-files
    cp -r $SPECS_PATH/. $DEPLOY_DIR
}

# Verificações
ENV_ERROR=0
if [[ -z "$SPECS_PATH" ]]; then
    (( ENV_ERROR++ ))
    echo "#ENV_ERROR_$ENV_ERROR"
    echo "  Este script requer a variável de ambiente \$SPECS_PATH contendo um diretório válido."
    echo "  Esse diretório deve conter arquivos de especificações não publicados no repositório."
fi
if [[ ! -f "$SPECS_PATH/.env" ]]; then
    (( ENV_ERROR++ ))
    echo "#ENV_ERROR_$ENV_ERROR"
    echo "  O arquivo '.env' deve estar no diretório especificado na variável de ambiente \$SPECS_PATH."
    echo "  Esse arquivo deve conter as variáveis de ambiente necessárias para o funcionamento do sistema."
fi
if [ ! -d "$PRIVATE_FILES_PATH" ]; then
    (( ENV_ERROR++ ))
    echo "#ENV_ERROR_$ENV_ERROR"
    echo "  Este script requer a variável de ambiente \$PRIVATE_FILES_PATH contendo um diretório válido." 
    echo "  Esse diretório será usado para persistir os arquivos privados do sistema."
fi
if [ ! -d "$PUBLIC_FILES_PATH" ]; then
    (( ENV_ERROR++ ))
    echo "#ENV_ERROR_$ENV_ERROR"
    echo "  Este script requer a variável de ambiente \$PUBLIC_FILES_PATH contendo um diretório válido." 
    echo "  Esse diretório será usado para persistir os arquivos públicos do sistema."
fi
if [ -z "$SOURCE_REPO" ]; then
    (( ENV_ERROR++ ))
    echo "#ENV_ERROR_$ENV_ERROR"
    echo "  Este script requer a variável de ambiente \$SOURCE_REPO definida." 
    echo "  Essa variável deve conter a URL do repositório de origem."
fi
if [ -z "$STABLE_BRANCH" ]; then
    (( ENV_ERROR++ ))
    echo "#ENV_ERROR_$ENV_ERROR"
    echo "  Este script requer a variável de ambiente \$STABLE_BRANCH definida." 
    echo "  Essa variável deve conter o nome da branch estável considerada para atualizar PRs."
fi
if [ $ENV_ERROR -gt 0 ]; then
    exit 1
fi
if [[ $# -lt 1 || $# -gt 2 ]]; then
    echo "Erro: quantidade parâmetros incorreta."
    exibir_uso_correto
    exit 1
else
if [[ $# -ge 2 && ( $2 != '--build-only' && $2 != '--clone-only' ) ]]; then
    echo "Erro: segundo argumento é inválido."
    exibir_uso_correto
    exit 1
fi
fi

# Início das operações
DEPLOY_DIR=/opt/mapas-deployed-`date +%Y%m%d%H%M`
if [ ${1:0:5} = 'pull/' ]; then
   echo 'deploying PR#'${1:5:${#1}-5}
   git clone --recurse-submodules -b $STABLE_BRANCH $SOURCE_REPO $DEPLOY_DIR && cd $DEPLOY_DIR
   if [ $? -ne 0 ]; then exit $?; fi
   configurar_detalhes_minc
   git checkout -b teste-pr${1:5:${#1}-5}-atualizado && git fetch origin $1/head && git merge --no-ff --no-edit FETCH_HEAD
   if [ $? -ne 0 ]; then exit $?; fi
   echo 'Teste do PR#'${1:5:${#1}-5}' atualizado (commits:'`git rev-parse --short $STABLE_BRANCH`'+'`git rev-parse --short FETCH_HEAD`')' > version.txt
else
   echo 'deploying branch/tag '$1
   git clone --recurse-submodules -b $1 $SOURCE_REPO $DEPLOY_DIR
   if [ $? -ne 0 ]; then exit $?; fi
   configurar_detalhes_minc
fi
cd $DEPLOY_DIR
if [ -z $2 ]; then
   docker compose build
   if [ $? -ne 0 ]; then exit $?; fi
   docker stop $(docker ps -q)
   docker compose up -d
   echo 'Deployed tag "'$1'" on directory "'$DEPLOY_DIR'". It should be already running by now.'
else
if [ $2 = '--build-only' ]; then
   docker compose build
   echo 'Deployed tag "'$1'" on directory "'$DEPLOY_DIR'". Just built, will NOT run.'
else
if [ $2 = '--clone-only' ]; then
   echo 'Deployed tag "'$1'" on directory "'$DEPLOY_DIR'". Just source-code copied, not built neither run.'
fi
fi
fi
