#!/bin/bash
echo "
==========================================================================================
=================================== ATENÇÃO - CUIDADO ====================================
==========================================================================================
ESTA OPERAÇÃO APAGARAÁ O BANCO DE DADOS ATUAL E RECUPERARÁ A VERSÃO DO ARQUIVO db/dump.sql


PARA CONTINUAR ESCREVA 'sim': "
read confirm

if [[ $confirm == 'sim' ]]; then
	sudo -u postgres dropdb mapasculturais
	sudo -u postgres psql < ../db/dump.sql
fi;

