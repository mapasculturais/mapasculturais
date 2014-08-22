<?php
$this->layout = 'panel'
?>
<div class="main-content">

	<p id="boas-vindas-painel">
		Olá, <strong><?php echo $app->user->profile->name ?></strong>, bem-vindo ao painel do SPCultura!
	</p>
	<h2>Resumo</h2>
    <section id="estatisticas-do-usuario" class="clearfix">
    	<div class="box">
        	<div class="clearfix">
        		<span class="alignleft">Eventos</span>
        		<div class="icone icon_calendar alignright"></div>
        	</div>
        	<div class="clearfix">
        		<a class="valor hltip" href="<?php echo $app->createUrl('panel', 'events') ?>" title="Ver meus eventos"><?php echo $count->events;?></a>
        		<a class="icone icon_plus alignright hltip" href="<?php echo $app->createUrl('event', 'create'); ?>" title="Adicionar eventos"></a>
        	</div>
        </div>
        <div class="box">
        	<div class="clearfix">
        		<span class="alignleft">Agentes</span>
        		<div class="icone icon_profile alignright"></div>
        	</div>
        	<div class="clearfix">
        		<a class="valor hltip" href="<?php echo $app->createUrl('panel', 'agents') ?>" title="Ver meus agentes"><?php echo $count->agents;?></a>
        		<a class="icone icon_plus alignright hltip" href="<?php echo $app->createUrl('agent', 'create'); ?>" title="Adicionar agentes"></a>
        	</div>
        </div>
        <div class="box">
	        <div class="clearfix">
	        	<span class="alignleft">Espaços</span>
	        	<div class="icone icon_building alignright"></div>
	        </div>
	        <div class="clearfix">
	        	<a class="valor hltip" href="<?php echo $app->createUrl('panel', 'spaces') ?>" title="Ver meus espaços"><?php echo $count->spaces;?></a>
	        	<a class="icone icon_plus alignright hltip" href="<?php echo $app->createUrl('space', 'create'); ?>" title="Adicionar espaços"></a>
	        </div>
	    </div>
        <div class="box">
        	<div class="clearfix">
        		<span class="alignleft">Projetos</span>
        		<div class="icone icon_document_alt alignright"></div>
        	</div>
        	<div class="clearfix">
        		<a class="valor hltip" href="<?php echo $app->createUrl('panel', 'projects') ?>" title="Ver meus projetos"><?php echo $count->projects;?></a>
        		<a class="icone icon_plus alignright hltip" href="<?php echo $app->createUrl('project', 'create'); ?>" title="Adicionar projetos"></a>
        	</div>
        </div>

    </section>
    <section id="atividades" class="staging-hidden">
		<header class="clearfix">
			<h2 class="alignleft">Atividades</h2>
			<div id="status-das-atividades" class="dropdown alignright">
				<div class="placeholder">Pendentes</div>
                <div class="submenu-dropdown">
                    <ul>
						<li>Pendentes</li>
						<li>Todas</li>
					</ul>
                </div>
			</div>
        </header>
        <div class="atividade clearfix">
			<p>
				<span class="small">Em 00/00/00 - 00:00</span><br/>
				<a href="#">Fulano</a> adicionou o evento <a href="#">Mussum Ipsun</a> em seu espaço <a href="#">Teatro Pindureta</a>".
			</p>
            <div><a class="action" href="#">aceitar</a> <a class="action" href="#">rejeitar</a></div>
        </div>
        <div class="atividade clearfix">
			<p>
				<span class="small">Em 00/00/00 - 00:00</span><br/>
				<a href="#">Fulano</a> adicionou o evento <a href="#">Mussum Ipsun</a> em seu espaço <a href="#">Teatro Pindureta</a>".
			</p>
            <div><a class="action" href="#">aceitar</a> <a class="action" href="#">rejeitar</a></div>
        </div>
        <div class="atividade clearfix">
			<p>
				<span class="small">Em 00/00/00 - 00:00</span><br/>
				<a href="#">Fulano</a> adicionou o evento <a href="#">Mussum Ipsun</a> em seu espaço <a href="#">Teatro Pindureta</a>".
			</p>
            <div><a class="action" href="#">aceitar</a> <a class="action" href="#">rejeitar</a></div>
        </div>
        <div class="atividade clearfix">
			<p>
				<span class="small">Em 00/00/00 - 00:00</span><br/>
				<a href="#">Fulano</a> adicionou o evento <a href="#">Mussum Ipsun</a> em seu espaço <a href="#">Teatro Pindureta</a>".
			</p>
            <div><a class="action" href="#">aceitar</a> <a class="action" href="#">rejeitar</a></div>
        </div>
        <div class="atividade clearfix">
			<p>
				<span class="small">Em 00/00/00 - 00:00</span><br/>
				<a href="#">Fulano</a> adicionou o evento <a href="#">Mussum Ipsun</a> em seu espaço <a href="#">Teatro Pindureta</a>".
			</p>
            <div><a class="action" href="#">aceitar</a> <a class="action" href="#">rejeitar</a></div>
        </div>
        <div class="atividade clearfix">
			<p>
				<span class="small">Em 00/00/00 - 00:00</span><br/>
				<a href="#">Fulano</a> adicionou o evento <a href="#">Mussum Ipsun</a> em seu espaço <a href="#">Teatro Pindureta</a>".
			</p>
            <div><a class="action" href="#">aceitar</a> <a class="action" href="#">rejeitar</a></div>
        </div>
        <div class="atividade clearfix">
			<p>
				<span class="small">Em 00/00/00 - 00:00</span><br/>
				<a href="#">Fulano</a> adicionou o evento <a href="#">Mussum Ipsun</a> em seu espaço <a href="#">Teatro Pindureta</a>".
			</p>
            <div><a class="action" href="#">aceitar</a> <a class="action" href="#">rejeitar</a></div>
        </div>
        <div class="atividade clearfix">
			<p>
				<span class="small">Em 00/00/00 - 00:00</span><br/>
				<a href="#">Fulano</a> adicionou o evento <a href="#">Mussum Ipsun</a> em seu espaço <a href="#">Teatro Pindureta</a>".
			</p>
            <div><a class="action" href="#">aceitar</a> <a class="action" href="#">rejeitar</a></div>
        </div>
        <div class="atividade clearfix">
			<p>
				<span class="small">Em 00/00/00 - 00:00</span><br/>
				<a href="#">Fulano</a> adicionou o evento <a href="#">Mussum Ipsun</a> em seu espaço <a href="#">Teatro Pindureta</a>".
			</p>
            <div><a class="action" href="#">aceitar</a> <a class="action" href="#">rejeitar</a></div>
        </div>
        <div class="atividade clearfix">
			<p>
				<span class="small">Em 00/00/00 - 00:00</span><br/>
				<a href="#">Fulano</a> adicionou o evento <a href="#">Mussum Ipsun</a> em seu espaço <a href="#">Teatro Pindureta</a>".
			</p>
            <div><a class="action" href="#">aceitar</a> <a class="action" href="#">rejeitar</a></div>
        </div>
    </section>
</div>
<div class="ficha barra-direita">

</div>
