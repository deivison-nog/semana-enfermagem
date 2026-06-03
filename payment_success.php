<?php

declare(strict_types=1);

require __DIR__ . '/payment_template.php';

renderPaymentPage(
    'Pagamento aprovado',
    'Sua inscrição foi confirmada com sucesso. Guarde os dados de referência para acompanhamento.',
    'success',
    'approved'
);
