<?php

declare(strict_types=1);

session_start();

require __DIR__ . '/config.php';
require __DIR__ . '/helpers.php';

$errors = pullErrors();
$successMessage = pullSuccessMessage();
$categories = allowedCategories();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semana da Enfermagem – Município de Colares 2026</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="hero" id="topo">
    <div class="container">
        <nav class="topnav">
            <a href="#topo">Semana da Enfermagem 2026</a>
            <div>
                <a href="#sobre">Sobre</a>
                <a href="#programacao">Programação</a>
                <a href="#inscricao">Inscrição</a>
            </div>
        </nav>
        <section class="hero-grid">
            <div>
                <h1>Semana da Enfermagem – Município de Colares 2026</h1>
                <p class="subtitle">Cuidado, Humanização e Valorização Profissional no SUS</p>
                <ul class="event-meta">
                    <li><strong>Data:</strong> 17 de junho de 2026</li>
                    <li><strong>Local:</strong> Escola do Auditório José Malcher</li>
                    <li><strong>Horário:</strong> 08h00 às 13h00</li>
                </ul>
                <a class="btn btn-primary" href="#inscricao">Fazer inscrição</a>
            </div>
            <aside class="hero-card">
                <span class="tag">Inscrições abertas</span>
                <h2>Pagamento online via Mercado Pago</h2>
                <p>Garanta sua vaga com pagamento único de inscrição.</p>
                <p class="price">R$ 50,00</p>
                <a class="btn btn-secondary" href="#inscricao">Ir para pagamento</a>
            </aside>
        </section>
    </div>
</header>

<main>
    <section class="container section" id="sobre">
        <h2>Sobre o evento</h2>
        <p>A Semana da Enfermagem é uma importante ação de valorização profissional, educação permanente e fortalecimento das práticas assistenciais desenvolvidas pelos profissionais de enfermagem no Sistema Único de Saúde (SUS).</p>
        <p>No município de Colares, o evento promove integração, atualização técnica e reconhecimento profissional, reforçando o compromisso ético e humanizado no cuidado à população.</p>

        <div class="cards">
            <article class="card">
                <h3>Justificativa</h3>
                <p>A enfermagem possui papel fundamental na promoção, prevenção e recuperação da saúde. A iniciativa fortalece a qualidade da assistência e incentiva práticas seguras, éticas e humanizadas.</p>
            </article>
            <article class="card">
                <h3>Objetivos</h3>
                <ul>
                    <li>Promover valorização, capacitação e integração.</li>
                    <li>Incentivar educação permanente em saúde.</li>
                    <li>Fortalecer o trabalho em equipe.</li>
                    <li>Estimular práticas humanizadas no cuidado.</li>
                </ul>
            </article>
            <article class="card">
                <h3>Público-alvo</h3>
                <ul>
                    <li>Enfermeiros</li>
                    <li>Técnicos de enfermagem</li>
                    <li>Auxiliares de enfermagem</li>
                    <li>Acadêmicos da área da enfermagem</li>
                </ul>
            </article>
        </div>
    </section>

    <section class="container section" id="programacao">
        <h2>Programação</h2>
        <div class="schedule">
            <div><span>Credenciamento</span><strong>08h00 às 09h00</strong></div>
            <div><span>Palestra Magna</span><strong>09h00 às 10h00</strong></div>
            <div><span>Intervalo / Coffee Break</span><strong>10h00 às 10h30</strong></div>
            <div><span>Palestra municipal</span><strong>11h00 às 12h00</strong></div>
            <div><span>Encerramento</span><strong>12h00 às 13h00</strong></div>
        </div>
    </section>

    <section class="container section" id="inscricao">
        <h2>Inscrição online</h2>
        <p class="section-note">Preencha seus dados para gerar o checkout no Mercado Pago.</p>

        <?php if ($successMessage !== null): ?>
            <div class="alert alert-success"><?= h($successMessage) ?></div>
        <?php endif; ?>

        <div class="form-layout">
            <form class="card" method="post" action="process_registration.php" novalidate>
                <label>
                    Nome completo
                    <input type="text" name="nome" value="<?= h(old('nome')) ?>" required>
                    <?php if (isset($errors['nome'])): ?><small class="error"><?= h($errors['nome']) ?></small><?php endif; ?>
                </label>

                <label>
                    CPF
                    <input type="text" name="cpf" value="<?= h(old('cpf')) ?>" required>
                    <?php if (isset($errors['cpf'])): ?><small class="error"><?= h($errors['cpf']) ?></small><?php endif; ?>
                </label>

                <label>
                    E-mail
                    <input type="email" name="email" value="<?= h(old('email')) ?>" required>
                    <?php if (isset($errors['email'])): ?><small class="error"><?= h($errors['email']) ?></small><?php endif; ?>
                </label>

                <label>
                    Telefone
                    <input type="text" name="telefone" value="<?= h(old('telefone')) ?>" required>
                    <?php if (isset($errors['telefone'])): ?><small class="error"><?= h($errors['telefone']) ?></small><?php endif; ?>
                </label>

                <label>
                    Categoria
                    <select name="categoria" required>
                        <option value="">Selecione</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= h($category) ?>" <?= old('categoria') === $category ? 'selected' : '' ?>><?= h($category) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['categoria'])): ?><small class="error"><?= h($errors['categoria']) ?></small><?php endif; ?>
                </label>

                <?php if (isset($errors['checkout'])): ?><p class="error-block"><?= h($errors['checkout']) ?></p><?php endif; ?>

                <button class="btn btn-primary" type="submit">Continuar para pagamento</button>
            </form>

            <aside class="card summary">
                <h3>Resumo da inscrição</h3>
                <p><strong>Evento:</strong> Semana da Enfermagem – Município de Colares 2026</p>
                <p><strong>Tema:</strong> Cuidado, Humanização e Valorização Profissional no SUS</p>
                <p><strong>Data:</strong> 17/06/2026</p>
                <p><strong>Local:</strong> Escola do Auditório José Malcher</p>
                <p class="price">R$ 50,00</p>
                <p class="muted">Pagamento único via Mercado Pago.</p>
            </aside>
        </div>
    </section>

    <section class="container section">
        <h2>Organização do evento</h2>
        <div class="cards">
            <article class="card compact"><strong>Secretaria Municipal de Saúde</strong><p>ELIONAI ALMEIDA DE SOUSA</p></article>
            <article class="card compact"><strong>Coordenação da Atenção Básica</strong><p>ANA CLEO BORGES</p></article>
            <article class="card compact"><strong>Coordenação de Educação Permanente</strong><p>TAMYRES MARIA SANTOS DA SILVA</p></article>
            <article class="card compact"><strong>Coordenação de Enfermagem RT Unidade Mista</strong><p>MARINALVA CARDOSO FAVACHO</p></article>
            <article class="card compact"><strong>Equipe Administrativa</strong><p>Apoio institucional local</p></article>
        </div>
    </section>
</main>

<footer class="footer">
    <div class="container">
        <p>Secretaria Municipal de Saúde de Colares • Semana da Enfermagem 2026</p>
    </div>
</footer>
</body>
</html>
