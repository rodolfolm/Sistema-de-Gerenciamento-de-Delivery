<?php
 
class Cliente extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Cliente_model');
	} 

	/*
	 * Página de login e cadastro do cliente
	 */
	public function index()
	{
		if(!isset($_SESSION['idCliente'])) {

			$dados['title'] = "SGD - Login";
			$this->load->view('components/head.php', $dados);
			$this->load->view('entrar_cadastrar.php');

		} else {

			$dados['title'] = "SGD - Conta";
			$this->load->view('components/head.php', $dados);
			$this->load->view('cliente.php');

		}
	}

	/*
	 * Adicionando cliente
	 * Verificando o email, caso já esteja cadastrado ou bloqueado, a mensagem é exibida com o erro
	 * caso ainda não esteja, os dados são cadastrados.
	 */
	public function add()
	{
		$cliente = array(
			'nome' => $this->input->post('inputNomeCadastro'),
			'telefone' => $this->input->post('inputTelefoneCadastro'),
			'email' => $this->input->post('inputEmailCadastro'),
			'senha' => md5($this->input->post('inputSenhaCadastro')),
			'status' => 'ativo',
		);

		$cadastrado = $this->Cliente_model->verificar_email($cliente['email']);

		if ($cadastrado && $cadastrado['status'] != 'bloqueado') {

			$mensagem = 'Email já cadastrado! Por favor, entre em sua conta.';
			echo json_encode($mensagem);

		} elseif ($cadastrado && $cadastrado['status'] == 'bloqueado') {

			$mensagem = 'Usuário bloqueado. Por favor, entre em contato com o suporte.';
			echo json_encode($mensagem);

		} else {

			$this->Cliente_model->add_cliente($cliente);

			$mensagem = 'Cadastro realizado com sucesso! Por favor, entre em sua conta.';
			echo json_encode($mensagem);

		}

	}

	/*
	 * Logando cliente
	 * Verificando se o cliente esta cadastrado, caso esteja cadastrado e ativo o login é realizado
	 * caso esteja bloqueado ou ainda não tenha cadastro o usuário é informado
	 */
	public function login()
	{

		$cliente = array(
			'email' => $this->input->post('inputEmailLogin'),
			'senha' => md5($this->input->post('inputSenhaLogin'))
		);

		$logando = $this->Cliente_model->logar_cliente($cliente['email'],$cliente['senha']);

		if ($logando['status'] == 'ativo') {

			$this->session->set_userdata('idCliente',$logando['idCliente']);
			$this->session->set_userdata('nome',$logando['nome']);
			$this->session->set_userdata('telefone',$logando['telefone']);
			$this->session->set_userdata('email',$logando['email']);

			$this->index();

		} elseif ($logando['status'] == 'bloqueado') {

			$mensagem = 'Usuário inativo ou bloqueado. Por favor, entre em contato com o suporte.';
			echo json_encode($mensagem);

		} else {

			$mensagem = 'Email ou senha incorretos!';
			echo json_encode($mensagem);

		}
	}  

	public function logout()
	{
		$this->session->sess_destroy();
		$this->index();
	}

	/*
	 * Editando cliente
	 */
	public function edit($idCliente)
	{   

		$data['cliente'] = $this->Cliente_model->get_cliente($idCliente);
		
		if(isset($data['cliente']['idCliente'])) {
			$params = array(
				'nome' => $this->input->post('nome'),
				'telefone' => $this->input->post('telefone'),
				'email' => $this->input->post('email'),
				'senha' => $this->input->post('senha'),
				'status' => $this->input->post('status'),
			);

			$this->Cliente_model->update_cliente($idCliente,$params);            
			return true;
		} else {
			return false;
		}
	} 
	
}