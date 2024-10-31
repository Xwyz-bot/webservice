<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Home Page
 *
 *
 * @package		FixBook
 * @category	Controller
 * @author		Luigi Verzì
*/

class Home extends CI_Controller
{
    // THE CONSTRUCTOR //
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Login_model');
        $this->load->model('Gestione_model');
        $this->load->model('Impostazioni_model');
        $this->lang->load('global', $this->Impostazioni_model->get_lingua());
        $this->lang->load('guest', $this->Impostazioni_model->get_lingua());
        $this->load->helper('cookie');
        $this->Impostazioni_model->gen_token();
    }

    // SHOW THE HOME PAGE //
    public function index()
    {
        $data['impostazioni'] = $this->Impostazioni_model->lista_impostazioni();
        if ($this->session->userdata('LoggedIn')) {
            $cookie= array(
                'name'   => 'waslogged',
                'value'  => TRUE,
                'expire' => '86500',
            );
            $this->input->set_cookie($cookie);
            $data['lista'] = $this->Gestione_model->lista_oggetti();
            $data['n_ordini'] = $this->Gestione_model->conta_ordini();
            $data['n_riparazioni'] = $this->Gestione_model->conta_riparazioni();
            $data['n_clienti'] = $this->Gestione_model->conta_clienti();
            $data['lista_c'] = $this->Gestione_model->lista_clienti();
            $this->load->view('home_page', $data);
        } else {
            redirect('login');
        }
    }

    // SHOW A INVOICE TEMPLATE //
    public function invoice($id,$tipo)
    {
        if ($this->session->userdata('LoggedIn')) {
            $data['impostazioni'] = $this->Impostazioni_model->lista_impostazioni();
            $data['db'] = $this->Gestione_model->trova_oggetto($id);
            $data['cliente'] = $this->Gestione_model->trova_cliente($this->Gestione_model->id_from_name($data['db']['Nominativo']));
            $data['valuta'] = $this->Impostazioni_model->get_currency();
            $data['lingua'] = $this->Impostazioni_model->get_lingua();
            if($tipo == 1) $this->load->view('template/invoice_template', $data);
            else $this->load->view('template/resoconto_template', $data);
        }
    }

    // SEND A SMS DIRECT //
    public function send_sms()
    {
        if ($this->session->userdata('LoggedIn')) 
        {
            $testo = $this->input->post('testo', true);
            $numero = $this->input->post('numero', true);
            $return = $this->Gestione_model->send_sms($numero, $testo);
            $stato = false;

            if(is_array($return)) { if($return['status'] != 'failed') $stato = true; }
            else { if($return->IsError != true) $stato = true; }

            echo json_encode(array('stato' => $stato));

        } else  redirect('');
    }

    // GENERATE THE JAVASCRIPT DYNAMIC FILE //
    public function js($name)
    {
        $data['impostazioni'] = $this->Impostazioni_model->lista_impostazioni();
        $data['valuta'] = $this->Impostazioni_model->get_currency();
        if($this->input->cookie('waslogged',true) == 1) $data['admin'] = 1;
        else $data['admin'] = 0;
        
        $this->output
            ->set_content_type('application/javascript');
        
        $this->load->view('js/'.$name.'_js', $data);
    }

    // GENERATE THE JAVASCRIPT DYNAMIC FILE //

    // GENERATE THE CUSTOM COLOR FILE //
    public function stile()
    {
        $data['colore'] = $this->input->post('colore', true);
        $data['alfa'] = $this->Impostazioni_model->hex2rgba($this->input->post('colore', true), 0.1);
        $this->load->view('js/colori_js', $data);
    }

    // GENERATE THE AJAX TABLE CONTENT //
    public function ajax($table, $id_nome = null)
    {
        if ($this->session->userdata('LoggedIn')) {
            $data['impostazioni'] = $this->Impostazioni_model->lista_impostazioni();
            if($table == 1) 
            {
                if($id_nome == null) $data['lista'] = $this->Gestione_model->lista_oggetti();
                else $data['lista'] = $this->Gestione_model->lista_oggetti($id_nome);
            }
            else $data['lista'] = $this->Gestione_model->lista_clienti();
            $data['table'] = $table;
            $this->load->view('ajax_table_page', $data);
        } else {
            $this->load->view('login_page');
        }
    }
    // OPEN NEW ORDER //
    public function apri_ordine()
    {
        if ($this->session->userdata('LoggedIn')) {
            $nominativo = $this->Gestione_model->name_from_id($this->input->post('nominativo', true));
            $idnominativo = $this->input->post('nominativo', true);
            $telefono = $this->Gestione_model->number_from_id($idnominativo);
            $categoria = $this->input->post('categoria', true);
            $modello = $this->input->post('modello', true);
            $guasto = $this->input->post('guasto', true);
            $pezzo = $this->input->post('pezzo', true);
            $anticipo = $this->input->post('anticipo', true);
            $prezzo = $this->input->post('prezzo', true);
            $tipo = $this->input->post('tipo', true);
            $sms = $this->input->post('sms', true);
            $commenti = $this->input->post('commenti', true);
            $codice = $this->input->post('codice', true);
            $status = $this->input->post('status', true);
            $custom = $this->input->post('custom', true);
            $token = $this->input->post('token', true);

            if($_SESSION['token'] != $token) die('CSRF Attempts');

            $this->add_new_cat($categoria); // ADD CATEGORY IF NOT EXISTS //

            $data = $this->Gestione_model->inserisci_ordine($nominativo, $idnominativo, $telefono, $categoria, $modello, $guasto, $pezzo, $anticipo, $prezzo, $tipo, $sms, $commenti, $status, $custom, $codice);

            echo json_encode($data);
        } else {
            redirect('');
        }
    }

    // EDIT ORDER //
    public function modifica_ordine()
    {
        if ($this->session->userdata('LoggedIn')) {
            $nominativo = $this->Gestione_model->name_from_id($this->input->post('nominativo', true));
            $idnominativo = $this->input->post('nominativo', true);
            $telefono = $this->Gestione_model->number_from_id($idnominativo);
            $categoria = $this->input->post('categoria', true);
            $modello = $this->input->post('modello', true);
            $guasto = $this->input->post('guasto', true);
            $pezzo = $this->input->post('pezzo', true);
            $anticipo = $this->input->post('anticipo', true);
            $prezzo = $this->input->post('prezzo', true);
            $tipo = $this->input->post('tipo', true);
            $id = $this->input->post('id', true);
            $sms = $this->input->post('sms', true);
            $commenti = $this->input->post('commenti', true);
            $codice = $this->input->post('codice', true);
            $status = $this->input->post('status', true);
            $custom = $this->input->post('custom', true);
            $token = $this->input->post('token', true);

            if($_SESSION['token'] != $token) die('CSRF Attempts');

            $this->add_new_cat($categoria); // ADD CATEGORY IF NOT EXISTS //

            echo $this->Gestione_model->salva_ordine($nominativo, $idnominativo, $telefono, $categoria, $modello, $guasto, $pezzo, $anticipo, $prezzo, $tipo, $id, $sms, $commenti, $status, $custom, $codice);
        } else {
            redirect('');
        }
    }

    // GET AN ORDER/REPARATION FOR SHOW IT //
    public function prendi_oggetto()
    {
        $id = $this->input->post('id', true);
        $data = $this->Gestione_model->trova_oggetto($id);
        echo json_encode($data);
    }

    // SET THE ORDER STATUS: WORK IN PROGRESS //
    public function inriparazione()
    {
        $id = $this->input->post('id', true);
        $token = $this->input->post('token', true);

        if($_SESSION['token'] != $token) die('CSRF Attempts');

        $data = $this->Gestione_model->inriparazione_oggetto($id);
        echo json_encode($data);
    }

    // SET THE ORDER STATUS: COMPLETE //
    public function completa()
    {
        $id = $this->input->post('id', true);
        $token = $this->input->post('token', true);

        if($_SESSION['token'] != $token) die('CSRF Attempts');

        $data = $this->Gestione_model->completa_oggetto($id);
        echo json_encode($data);
    }

    // SET THE ORDER STATUS: APPROVED //
    public function approva()
    {
        $id = $this->input->post('id', true);
        $token = $this->input->post('token', true);

        if($_SESSION['token'] != $token) die('CSRF Attempts');

        $data = $this->Gestione_model->approva_oggetto($id);
        echo json_encode($data);
    }

    // SET THE ORDER STATUS: TO DELIVER //
    public function daconsegnare()
    {
        $id = $this->input->post('id', true);
        $token = $this->input->post('token', true);

        if($_SESSION['token'] != $token) die('CSRF Attempts');

        $data = $this->Gestione_model->daconsegnare_oggetto($id);
        echo json_encode($data);
    }

    // DELETE AN ORDER //
    public function elimina()
    {
        $id = $this->input->post('id', true);
        $token = $this->input->post('token', true);

        if($_SESSION['token'] != $token) die('CSRF Attempts');

        $data = $this->Gestione_model->elimina_oggetto($id);
        echo json_encode($data);
    }

    // ADD NEW CAT IF NOT EXISTS //
    public function add_new_cat($cat)
    {
        $impostazioni = $this->Impostazioni_model->lista_impostazioni();
        $exist = 0;
        foreach(preg_split("/((\r?\n)|(\r\n?))/", $impostazioni['0']['categorie']) as $line){
            if($line == $cat) $exist = 1;
        }
        if($exist == 0) echo $this->Impostazioni_model->add_category($cat);
    }
}

/* End of file home.php */
/* Location: ./system/application/controllers/home.php */