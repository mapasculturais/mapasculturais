<?php $app->view->layout = 'default'; ?>
<div id="busca-avancada" class="clearfix">
    <div id="filtro-projetos" class="filtro-objeto clearfix">
    <div class="filtro">
        <span class="label">Tipo</span>
        <div class="dropdown">
            <div class="placeholder">Selecione os tipos</div>
            <div class="submenu-dropdown">
                <ul class="lista-de-filtro">
                    <li>Festival</li>
                    <li>Encontro</li>
                    <li>Sarau</li>
                    <li>Reunião</li>
                    <li>Mostra</li>
                    <li>Convenção</li>
                    <li>Ciclo</li>
                    <li>Programa</li>
                    <li>Edital</li>
                    <li>Concurso</li>
                    <li>Outros</li>
                </ul>
            </div>
        </div>
    </div>
    <!--.filtro-->
    <div class="filtro">
        <span class="icone icon_check"></span><span id="label-das-inscricoes" class="label">Inscrições Abertas</span>
    </div>
    <!--.filtro-->
    <form id="form-agente" class="filtro">
        <label for="nome-do-agente">Agente</label>
        <input class="autocomplete" name="nome-do-agente" type="text" placeholder="Agente" />
        <a class="botao principal" href="#">Listar agentes</a>
    </form>
    <!-- #form-projeto-->
    <form id="form-espaco" class="filtro">
        <label for="nome-do-espaco">Espaço</label>
        <input class="autocomplete" name="nome-do-espaco" type="text" placeholder="Espaço" />
        <a class="botao principal" href="#">Listar espaços</a>
    </form>
    <!-- #form-projeto-->
</div>
	<!--#filtro-projetos-->
	<div class="wrap clearfix">
		<form class="form-palavra-chave filtro-geral">
			<label for="busca">Palavra-chave</label>
			<input class="campo-de-busca" type="text" name="busca" placeholder="Digite um palavra-chave" />
		</form>
		<!--#busca-->
		<div id="filtro-prefeitura" class="filtro-geral">
			<a class="hltip botao principal" href="#" title="Exibir somente resultados da Secretaria Municipal de Cultura">Resultados da SMC</a>
		</div>
		<!-- #filtro-prefeitura-->
	</div>
</div>
<!--#busca-avancada-->
<div id="header-dos-resultados" class="clearfix">
	<div id="filtros-selecionados">
		<a class="tag tag-projeto" href="#">palavra-chave</a><a class="tag tag-projeto" href="#">inscrições abertas</a><a class="tag tag-projeto" href="#">música</a>
		<a class="tag remover-tudo" href="#">Remover todos filtros</a>
	</div>
    <div id="resultados"><strong>00</strong> projetos.</div>
	<div id="ferramentas">
		<div id="compartilhar">
			<a class="botao-de-icone icone social_share" href="#"></a>
			<form id="compartilhar-url"><div class="setinha"></div><label for="url-da-busca">Compartilhar esse resultado: </label><input id="url-da-busca" name="url-da-busca" type="text" value="http://lorem.ipsum.mussum/#filtro+filtro+filtro" /><a href="#" class="icone social_twitter"></a><a href="#" class="icone social_facebook"></a><a href="#" class="icone social_googleplus"></a></form>
		</div>
    </div>
</div>
<header id="header-dos-projetos" class="header-do-objeto clearfix">
    <div class="clearfix">
        <h1><span class="icone icon_document_alt"></span> Projetos</h1>
        <a class="botao adicionar" href="#">Adicionar projeto</a>
        <a class="icone arrow_carrot-down" href="#"></a>
    </div>
</header>
<div class="lista">
    <article class="objeto projeto clearfix">
        <h1><a href="<?php echo $this->controller->createUrl('single')?>">Título superlongo do projeto bem comprido demais pra caramba pra xuxu</a></h1>
        <div class="objeto-content clearfix">
            <div class="objeto-thumb"></div>
            <p class="objeto-resumo">
				Atirei o pau no gatis. Viva Forevis aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Copo furadis é disculpa de babadis, arcu quam euismod magna, bibendum egestas augue arcu ut est. Delegadis gente finis.
			</p>
			<div class="objeto-meta">
				<div><span class="label">Tipo:</span> <a href="#">Música</a></div>
				<div><span class="label">Organização:</span> <a href="#">Fulano de Tal</a></div>
				<div><span class="label">Inscrições:</span> 00/00/00 - 00/00/00</div>
            </div>
        </div>
    </article>
    <!--.objeto-->
    <article class="objeto projeto clearfix">
        <h1><a href="<?php echo $this->controller->createUrl('single')?>">Título superlongo do projeto bem comprido demais pra caramba pra xuxu</a></h1>
        <div class="objeto-content clearfix">
            <div class="objeto-thumb"></div>
            <p class="objeto-resumo">
				Atirei o pau no gatis. Viva Forevis aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Copo furadis é disculpa de babadis, arcu quam euismod magna, bibendum egestas augue arcu ut est. Delegadis gente finis.
			</p>
			<div class="objeto-meta">
				<div><span class="label">Tipo:</span> <a href="#">Música</a></div>
				<div><span class="label">Organização:</span> <a href="#">Fulano de Tal</a></div>
				<div><span class="label">Inscrições:</span> 00/00/00 - 00/00/00</div>
            </div>
        </div>
    </article>
    <!--.objeto-->
    <article class="objeto projeto clearfix">
        <h1><a href="<?php echo $this->controller->createUrl('single')?>">Título superlongo do projeto bem comprido demais pra caramba pra xuxu</a></h1>
        <div class="objeto-content clearfix">
            <div class="objeto-thumb"></div>
            <p class="objeto-resumo">
				Atirei o pau no gatis. Viva Forevis aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Copo furadis é disculpa de babadis, arcu quam euismod magna, bibendum egestas augue arcu ut est. Delegadis gente finis.
			</p>
			<div class="objeto-meta">
				<div><span class="label">Tipo:</span> <a href="#">Música</a></div>
				<div><span class="label">Organização:</span> <a href="#">Fulano de Tal</a></div>
				<div><span class="label">Inscrições:</span> 00/00/00 - 00/00/00</div>
            </div>
        </div>
    </article>
    <!--.objeto-->
    <article class="objeto projeto clearfix">
        <h1><a href="<?php echo $this->controller->createUrl('single')?>">Título superlongo do projeto bem comprido demais pra caramba pra xuxu</a></h1>
        <div class="objeto-content clearfix">
            <div class="objeto-thumb"></div>
            <p class="objeto-resumo">
				Atirei o pau no gatis. Viva Forevis aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Copo furadis é disculpa de babadis, arcu quam euismod magna, bibendum egestas augue arcu ut est. Delegadis gente finis.
			</p>
			<div class="objeto-meta">
				<div><span class="label">Tipo:</span> <a href="#">Música</a></div>
				<div><span class="label">Organização:</span> <a href="#">Fulano de Tal</a></div>
				<div><span class="label">Inscrições:</span> 00/00/00 - 00/00/00</div>
            </div>
        </div>
    </article>
    <!--.objeto-->
    <article class="objeto projeto clearfix">
        <h1><a href="<?php echo $this->controller->createUrl('single')?>">Título superlongo do projeto bem comprido demais pra caramba pra xuxu</a></h1>
        <div class="objeto-content clearfix">
            <div class="objeto-thumb"></div>
            <p class="objeto-resumo">
				Atirei o pau no gatis. Viva Forevis aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Copo furadis é disculpa de babadis, arcu quam euismod magna, bibendum egestas augue arcu ut est. Delegadis gente finis.
			</p>
			<div class="objeto-meta">
				<div><span class="label">Tipo:</span> <a href="#">Música</a></div>
				<div><span class="label">Organização:</span> <a href="#">Fulano de Tal</a></div>
				<div><span class="label">Inscrições:</span> 00/00/00 - 00/00/00</div>
            </div>
        </div>
    </article>
    <!--.objeto-->
    <article class="objeto projeto clearfix">
        <h1><a href="<?php echo $this->controller->createUrl('single')?>">Título superlongo do projeto bem comprido demais pra caramba pra xuxu</a></h1>
        <div class="objeto-content clearfix">
            <div class="objeto-thumb"></div>
            <p class="objeto-resumo">
				Atirei o pau no gatis. Viva Forevis aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Copo furadis é disculpa de babadis, arcu quam euismod magna, bibendum egestas augue arcu ut est. Delegadis gente finis.
			</p>
			<div class="objeto-meta">
				<div><span class="label">Tipo:</span> <a href="#">Música</a></div>
				<div><span class="label">Organização:</span> <a href="#">Fulano de Tal</a></div>
				<div><span class="label">Inscrições:</span> 00/00/00 - 00/00/00</div>
            </div>
        </div>
    </article>
    <!--.objeto-->
    <article class="objeto projeto clearfix">
        <h1><a href="<?php echo $this->controller->createUrl('single')?>">Título superlongo do projeto bem comprido demais pra caramba pra xuxu</a></h1>
        <div class="objeto-content clearfix">
            <div class="objeto-thumb"></div>
            <p class="objeto-resumo">
				Atirei o pau no gatis. Viva Forevis aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Copo furadis é disculpa de babadis, arcu quam euismod magna, bibendum egestas augue arcu ut est. Delegadis gente finis.
			</p>
			<div class="objeto-meta">
				<div><span class="label">Tipo:</span> <a href="#">Música</a></div>
				<div><span class="label">Organização:</span> <a href="#">Fulano de Tal</a></div>
				<div><span class="label">Inscrições:</span> 00/00/00 - 00/00/00</div>
            </div>
        </div>
    </article>
    <!--.objeto-->
    <article class="objeto projeto clearfix">
        <h1><a href="<?php echo $this->controller->createUrl('single')?>">Título superlongo do projeto bem comprido demais pra caramba pra xuxu</a></h1>
        <div class="objeto-content clearfix">
            <div class="objeto-thumb"></div>
            <p class="objeto-resumo">
				Atirei o pau no gatis. Viva Forevis aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Copo furadis é disculpa de babadis, arcu quam euismod magna, bibendum egestas augue arcu ut est. Delegadis gente finis.
			</p>
			<div class="objeto-meta">
				<div><span class="label">Tipo:</span> <a href="#">Música</a></div>
				<div><span class="label">Organização:</span> <a href="#">Fulano de Tal</a></div>
				<div><span class="label">Inscrições:</span> 00/00/00 - 00/00/00</div>
            </div>
        </div>
    </article>
    <!--.objeto-->
    <article class="objeto projeto clearfix">
        <h1><a href="<?php echo $this->controller->createUrl('single')?>">Título superlongo do projeto bem comprido demais pra caramba pra xuxu</a></h1>
        <div class="objeto-content clearfix">
            <div class="objeto-thumb"></div>
            <p class="objeto-resumo">
				Atirei o pau no gatis. Viva Forevis aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Copo furadis é disculpa de babadis, arcu quam euismod magna, bibendum egestas augue arcu ut est. Delegadis gente finis.
			</p>
			<div class="objeto-meta">
				<div><span class="label">Tipo:</span> <a href="#">Música</a></div>
				<div><span class="label">Organização:</span> <a href="#">Fulano de Tal</a></div>
				<div><span class="label">Inscrições:</span> 00/00/00 - 00/00/00</div>
            </div>
        </div>
    </article>
    <!--.objeto-->
    <article class="objeto projeto clearfix">
        <h1><a href="<?php echo $this->controller->createUrl('single')?>">Título superlongo do projeto bem comprido demais pra caramba pra xuxu</a></h1>
        <div class="objeto-content clearfix">
            <div class="objeto-thumb"></div>
            <p class="objeto-resumo">
				Atirei o pau no gatis. Viva Forevis aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Copo furadis é disculpa de babadis, arcu quam euismod magna, bibendum egestas augue arcu ut est. Delegadis gente finis.
			</p>
			<div class="objeto-meta">
				<div><span class="label">Tipo:</span> <a href="#">Música</a></div>
				<div><span class="label">Organização:</span> <a href="#">Fulano de Tal</a></div>
				<div><span class="label">Inscrições:</span> 00/00/00 - 00/00/00</div>
            </div>
        </div>
    </article>
    <!--.objeto-->
</div>
<!--.lista-->
