<?php

declare(strict_types=1);

require __DIR__ . '/payment_template.php';

renderPaymentPage(
    'Pagamento não concluído',
    'Não foi possível concluir o pagamento. Você pode voltar ao site e tentar novamente.',
    'failure',
    'failed'
);
