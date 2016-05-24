#!/bin/bash


DIR="$( cd "$( dirname "${BASH_SOURCE[0]}")"/../db && pwd )"

DB="mapasculturais"
USER=$(whoami)
OWNER=$(whoami)
FILE="dump.sql"

for i in "$@"
do
case $i in
    -u=*|--user=*)
	    USER="${i#*=}"
	    shift # past argument=value
    ;;
    -db=*|--database=*)
	    DB="${i#*=}"
	    shift # past argument=value
    ;;
    -f=*|--filename=*)
	    FILE="${i#*=}"
	    shift # past argument=value
    ;;
    -o=*|--owner=*)
	    OWNER="${i#*=}"
	    shift # past argument=value
    ;;
    -h|--help)
    	    echo "
	restore-dump.sh [-u=postgres] [-f=dump.sql] [-db=mapasculturais] [-o=mapasculturais]

	-u=  | --user= 		usuário que executará o comando (padrão: whoami)
	-f=  | --filename=	arquivo de dump que deve ser importado. (padrão: dump.sql)
	-db= | --database=	nome da base de dados de destino (padrão: mapasculturais)
	-o=  | --owner=		owner da base que será criada (padrão: whoami)
    	    "
    	    exit
    ;;
esac
done


echo "
=========================================================================================
=================================== ATENÇÃO - CUIDADO ===================================
=========================================================================================
ESTA OPERAÇÃO APAGARÁ O BANCO DE DADOS ATUAL E RECUPERARÁ A VERSÃO DO ARQUIVO $FILE


PARA CONTINUAR ESCREVA 'sim': "
read confirm


if [[ $confirm == 'sim' ]]; then
	sudo -u $USER dropdb $DB
	sudo -u $USER createdb $DB --owner $OWNER
	sudo -u $USER psql -d $DB < "$DIR/$FILE"
fi;
