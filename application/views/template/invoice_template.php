<?php
$tasse = ($impostazioni[0]['tax'] / 100) * $db['Prezzo'];
$senza_tasse = ($db['Prezzo']) - (($impostazioni[0]['tax'] / 100) * $db['Prezzo']); // PRICE WITHOUT TAX
$totale = $db['Prezzo']; // PRICE WITH TAX
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title><?= $this->lang->line('invoice');?></title>
        <link href="<?= site_url('css/invoice.css'); ?>" rel="stylesheet">
        <script src="<?=site_url('js/jquery.js'); ?>"></script>
    </head>
    <?php 
$colore = $impostazioni[0]['colore_prim'];
echo '<style id="colori">';
include FCPATH.'application/views/js/colori_js.php';
echo '</style>';
    ?>
    <body>
        <div id="editable_invoice"><?= $this->lang->line('editable_invoice');?></div>
        <header class="clearfix">
            <div id="company" contentEditable="true">
                <h2 class="name"><?= $impostazioni[0]['titolo']; ?></h2>
                <div><?= $impostazioni[0]['invoice_name']; ?></div>
                <div><?= $impostazioni[0]['indirizzo']; ?></div>
                <div><?= $impostazioni[0]['telefono']; ?></div>
                <div><a href="mailto:<?= $impostazioni[0]['invoice_mail']; ?>"><?= $impostazioni[0]['invoice_mail']; ?></a></div>
                <div><?= $impostazioni[0]['vat']; ?></div>
            </div>
            </div>
        </header>
    <main>
        <div id="details" class="clearfix">
            <div id="client" contentEditable="true">
                <div class="to"><?= $this->lang->line('Cliente_t');?>:</div>
                <h2 class="name"><?=$db['Nominativo']; ?></h2>
                
                <div class="vat">
                    <?php 
if(isset($cliente['vat']))
{
    echo $cliente['vat']; 
    $ve = 1;
}
if(isset($cliente['cf']))
{
    if($ve) echo ' / ';
    echo $cliente['cf'];
}
                    ?>
                </div>
            </div>
            <div id="invoice" contentEditable="true">
                <h1><?= $this->lang->line('invoice_n');?> <i>0000</i></h1>
                <div class="date"><?= $this->lang->line('data_fattura');?>: <?= date_format(date_create($db['dataChiusura']),"Y/m/d"); ?></div>
            </div>
        </div>
        <table border="0" cellspacing="0" cellpadding="0">
            <thead>
                <tr>
                    <th class="no">#</th>
                    <th class="desc"><?= $this->lang->line('descrizione');?></th>
                    <th class="unit"><?= $this->lang->line('Prezzo_t');?></th>
                    <th class="qty"><?= $this->lang->line('quantity');?></th>
                    <th class="total"><?= $this->lang->line('Prezzo_t');?></th>
                </tr>
            </thead>
            <tbody contentEditable="true">
                <tr>
                    <td class="no">01</td>
                    <td class="desc"><h3><?php if($db['Tipo'] == 2) {echo $this->lang->line('js_tipo_riparazione').': '.$db['Guasto'].' '.$db['Modello']; } else { echo $db['Pezzo'].' '.$db['Modello'];} ?></td>
                    <td class="unit"><?=$this->Impostazioni_model->get_money($senza_tasse);?></td>
                    <td class="qty">1</td>
                    <td class="total"><?=$this->Impostazioni_model->get_money($senza_tasse);?></td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" rowspan="3" id="commenti"><textarea id="commento" onkeyup="auto_grow(this)" contentEditable="true"><?=$db['Commenti']; ?></textarea></td>
                    <td colspan="2"><?= $this->lang->line('subtotal');?></td>
                    <td contentEditable="true"><?=$this->Impostazioni_model->get_money($senza_tasse);?></td>
                </tr>
                <tr>
                    <td colspan="2"><?= $this->lang->line('tax');?> <?= $impostazioni[0]['tax']; ?>%</td>
                    <td contentEditable="true"><?=$this->Impostazioni_model->get_money($tasse);?></td>
                </tr>
                <tr>
                    <td colspan="2"><?= $this->lang->line('total_inv');?></td>
                    <td contentEditable="true"><?=$this->Impostazioni_model->get_money($totale);?></td>
                </tr>
            </tfoot>
        </table>
    </main>
    <footer>
        <?= $impostazioni[0]['disclaimer']; ?>
    </footer>

    <div id="print_button"><?= $this->lang->line('print_invoice');?></div>

    </body>

<script>
    jQuery(document).on("click", "#print_button", function() {
        var num = jQuery(this).data("num");
        toastr['success']("<?= $this->lang->line('js_stampa_in_corso');?>");
        window.print();
        setInterval(function() {
            window.close();
        }, 500);
    });
    function auto_grow(element) {
        element.style.height = "5px";
        element.style.height = (element.scrollHeight)+"px";
    }
    auto_grow(document.getElementById("commento"));
</script>
<link rel="stylesheet" href="<?= site_url('assets/css/toastr.min.css'); ?>">
<script src="<?= site_url('assets/js/toastr.min.js'); ?>"></script>

</html>

