<h1><?php echo \MapasCulturais\i::__('Autenticação Fake'); ?></h1>
<style>
    * {
        -webkit-box-sizing: border-box;
        -moz-box-sizing: border-box;
        box-sizing: border-box;
    }
    label, select, input[type="text"], input[type="email"]{
        display: block;
        height: initial;
        width: 100%;
    }
    #main-section{
        background: #fff;
        padding: 15px;
        border-radius: 3px;
    }
    hr{
        border: 0;
    }

    table {
        width: 100%;
        max-width: 100%;
        margin-bottom: 20px;
        border: 1px solid #ddd;
    }

    table>tbody>tr>td,
    table>tbody>tr>th,
    table>tfoot>tr>td,
    table>tfoot>tr>th,
    table>thead>tr>td,
    table>thead>tr>th {
        padding: 8px;
        line-height: 1.42857143;
        vertical-align: top;
        border-top: 1px solid #ddd;
        border: 1px solid #ddd;
    }

    table>thead>tr>th {
        vertical-align: bottom;
        border-bottom-width: 2px;
        border-top: 0;
    }

    table>tbody>tr>td {
        text-align: left;
        cursor: pointer;
    }

    table>tbody>tr:nth-of-type(odd) {
        background-color: #f9f9f9;
    }

    table>tbody>tr.active,
    table>tbody>tr:hover {
        background-color: #def3ff;
    }
</style>
<form method="GET" action="<?php echo $form_action ?>">
    <label>
        <?php echo \MapasCulturais\i::__('Filtrar usuário') ?>
        <br>
        <input type="text" id="agentFilter" />
    </label>
    <label>
        <?php echo \MapasCulturais\i::__('Login com usuario') ?>
        <br>
        <table>
            <thead>
                <tr>
                    <th style="width: 30px;"></th>
                    <th>ID</th>
                    <th>User E-mail</th>
                    <th>Agent Name (Profile)</th>
                    <th>Role</th>
                </tr>
            </thead>
            <tbody id="fake_user_id_options">
                <?php
                foreach ($users as $u):
                    $role = $u['role_name'];
                    ?>
                    <tr>
                        <td><input type="radio" name="fake_authentication_user_id" value="<?= $u['id'] ?>"></td>
                        <td><?= $u['id'] ?></td>
                        <td><?= $u['email'] ?></td>
                        <td><?= $u['profile'] ?></td>
                        <td><?= $u['role_name'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </label>
    <br />
    <input type="submit" value="<?php \MapasCulturais\i::esc_attr_e("Logar"); ?>" />
</form>

<hr/>
<form method="POST" action="<?php echo $new_user_form_action ?>">
    <h2><?php \MapasCulturais\i::_e("Criar novo usuário"); ?></h2>
    <p><label> <?php \MapasCulturais\i::_e("Name"); ?>: <input type="text" name="name" value="" /></label></p>
    <p><label> <?php \MapasCulturais\i::_e("E-mail"); ?>: <input type="email" name="email" value="" /></label></p>
    <input type="submit" value="<?php \MapasCulturais\i::esc_attr_e("Criar"); ?>"/>
</form>


<script>
    $(function () {
        var $inputFilter = $('#agentFilter');
        var $options = $('#fake_user_id_options');

        $options.delegate('tr', 'click', function (event) {

            $('tr', $options).removeClass('active');
            $(this).addClass('active');

            var that = this;
            setTimeout(function () {
                //$('input[name="fake_authentication_user_id"][value="' + SelectdValue + '"]').prop('checked', true);
                $('[name="fake_authentication_user_id"]', that).prop('checked', true);
            }, 10);

            event.stopPropagation();
            event.preventDefault();
            return false;
        });

        $inputFilter.keyup(function (fn, delay) {
            var timer = null;
            var showLoadingMessage;
            return function () {

                if (!showLoadingMessage) {
                    $options.empty();
                    showLoadingMessage = true;
                    $options.append([
                        '<tr>',
                        '    <td colspan="5"',
                        '       style="text-align: center; color: #0000cc; background: #ddddff;">',
                        '       <strong>Filtrando usuários</strong>',
                        '    </td>',
                        '</tr>'
                    ].join(''));
                }
                var context = this;
                var args = arguments;
                clearTimeout(timer);
                timer = setTimeout(function () {
                    showLoadingMessage = false;
                    fn.apply(context, args);
                }, delay);
            };
        }(function () {
            $.ajax('', {
                data: {
                    q: $inputFilter.val()
                },
                success: function (data) {
                    $options.empty();

                    if (data.length < 1) {
                        $options.append([
                            '<tr>',
                            '    <td colspan="5"',
                            '       style="text-align: center;color: #ee0000;background: #ffdddd;">',
                            '       <strong>Nenhum registro encontrado</strong>',
                            '    </td>',
                            '</tr>'
                        ].join(''));
                    } else {
                        $.each(data, function (i, item) {
                            $options.append([
                                '<tr>',
                                '    <td><input type="radio" name="fake_authentication_user_id" value="' + item['id'] + '"></td>',
                                '    <td>' + item['id'] + '</td>',
                                '    <td>' + item['email'] + '</td>',
                                '    <td>' + item['profile'] + '</td>',
                                '    <td>' + (item['role_name'] || '') + '</td>',
                                '</tr>'
                            ].join(''));
                        });
                    }
                }
            });
        }, 200));
    });
</script>