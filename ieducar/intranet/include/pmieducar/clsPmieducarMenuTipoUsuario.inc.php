<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*																	     *
*	@author Prefeitura Municipal de Itaja�								 *
*	@updated 29/03/2007													 *
*   Pacote: i-PLB Software P�blico Livre e Brasileiro					 *
*																		 *
*	Copyright (C) 2006	PMI - Prefeitura Municipal de Itaja�			 *
*						ctima@itajai.sc.gov.br					    	 *
*																		 *
*	Este  programa  �  software livre, voc� pode redistribu�-lo e/ou	 *
*	modific�-lo sob os termos da Licen�a P�blica Geral GNU, conforme	 *
*	publicada pela Free  Software  Foundation,  tanto  a vers�o 2 da	 *
*	Licen�a   como  (a  seu  crit�rio)  qualquer  vers�o  mais  nova.	 *
*																		 *
*	Este programa  � distribu�do na expectativa de ser �til, mas SEM	 *
*	QUALQUER GARANTIA. Sem mesmo a garantia impl�cita de COMERCIALI-	 *
*	ZA��O  ou  de ADEQUA��O A QUALQUER PROP�SITO EM PARTICULAR. Con-	 *
*	sulte  a  Licen�a  P�blica  Geral  GNU para obter mais detalhes.	 *
*																		 *
*	Voc�  deve  ter  recebido uma c�pia da Licen�a P�blica Geral GNU	 *
*	junto  com  este  programa. Se n�o, escreva para a Free Software	 *
*	Foundation,  Inc.,  59  Temple  Place,  Suite  330,  Boston,  MA	 *
*	02111-1307, USA.													 *
*																		 *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/**
* @author Prefeitura Municipal de Itaja�
*
* Criado em 23/06/2006 09:14 pelo gerador automatico de classes
*/

require_once( "include/pmieducar/geral.inc.php" );

class clsPmieducarMenuTipoUsuario
{
	var $ref_cod_tipo_usuario;
	var $ref_cod_menu_submenu;
	var $cadastra;
	var $visualiza;
	var $exclui;

	// propriedades padrao

	/**
	 * Armazena o total de resultados obtidos na ultima chamada ao metodo lista
	 *
	 * @var int
	 */
	var $_total;

	/**
	 * Nome do schema
	 *
	 * @var string
	 */
	var $_schema;

	/**
	 * Nome da tabela
	 *
	 * @var string
	 */
	var $_tabela;

	/**
	 * Lista separada por virgula, com os campos que devem ser selecionados na proxima chamado ao metodo lista
	 *
	 * @var string
	 */
	var $_campos_lista;

	/**
	 * Lista com todos os campos da tabela separados por virgula, padrao para selecao no metodo lista
	 *
	 * @var string
	 */
	var $_todos_campos;

	/**
	 * Valor que define a quantidade de registros a ser retornada pelo metodo lista
	 *
	 * @var int
	 */
	var $_limite_quantidade;

	/**
	 * Define o valor de offset no retorno dos registros no metodo lista
	 *
	 * @var int
	 */
	var $_limite_offset;

	/**
	 * Define o campo padrao para ser usado como padrao de ordenacao no metodo lista
	 *
	 * @var string
	 */
	var $_campo_order_by;


	/**
	 * Construtor (PHP 4)
	 *
	 * @return object
	 */
	function clsPmieducarMenuTipoUsuario( $ref_cod_tipo_usuario = null, $ref_cod_menu_submenu = null, $cadastra = null, $visualiza = null, $exclui = null )
	{
		$db = new clsBanco();
		$this->_schema = "pmieducar.";
		$this->_tabela = "{$this->_schema}menu_tipo_usuario";

		$this->_campos_lista = $this->_todos_campos = "ref_cod_tipo_usuario, ref_cod_menu_submenu, cadastra, visualiza, exclui";

		if( is_numeric( $ref_cod_menu_submenu ) )
		{
			if( class_exists( "clsMenuSubmenu" ) )
			{
				$tmp_obj = new clsMenuSubmenu( $ref_cod_menu_submenu );
				if( method_exists( $tmp_obj, "existe") )
				{
					if( $tmp_obj->existe() )
					{
						$this->ref_cod_menu_submenu = $ref_cod_menu_submenu;
					}
				}
				else if( method_exists( $tmp_obj, "detalhe") )
				{
					if( $tmp_obj->detalhe() )
					{
						$this->ref_cod_menu_submenu = $ref_cod_menu_submenu;
					}
				}
			}
			else
			{
				if( $db->CampoUnico( "SELECT 1 FROM menu_submenu WHERE cod_menu_submenu = '{$ref_cod_menu_submenu}'" ) )
				{
					$this->ref_cod_menu_submenu = $ref_cod_menu_submenu;
				}
			}
		}
		if( is_numeric( $ref_cod_tipo_usuario ) )
		{
			if( class_exists( "clsPmieducarTipoUsuario" ) )
			{
				$tmp_obj = new clsPmieducarTipoUsuario( $ref_cod_tipo_usuario );
				if( method_exists( $tmp_obj, "existe") )
				{
					if( $tmp_obj->existe() )
					{
						$this->ref_cod_tipo_usuario = $ref_cod_tipo_usuario;
					}
				}
				else if( method_exists( $tmp_obj, "detalhe") )
				{
					if( $tmp_obj->detalhe() )
					{
						$this->ref_cod_tipo_usuario = $ref_cod_tipo_usuario;
					}
				}
			}
			else
			{
				if( $db->CampoUnico( "SELECT 1 FROM pmieducar.tipo_usuario WHERE cod_tipo_usuario = '{$ref_cod_tipo_usuario}'" ) )
				{
					$this->ref_cod_tipo_usuario = $ref_cod_tipo_usuario;
				}
			}
		}


		if( is_numeric( $cadastra ) )
		{
			$this->cadastra = $cadastra;
		}
		if( is_numeric( $visualiza ) )
		{
			$this->visualiza = $visualiza;
		}
		if( is_numeric( $exclui ) )
		{
			$this->exclui = $exclui;
		}

	}


	/**
	 * Atualiza os usuarios quando um tipo de usuario e atualizado
	 *
	 * @return bool
	 */

	public function atualizaUsuarios($tipo){
		$db = new clsBanco();

		// Busca as permissoes desse tipo de usuario
		$sql = "SELECT ref_cod_menu_submenu, cadastra, exclui FROM menu_tipo_usuario WHERE ref_cod_tipo_usuario = $tipo";
		$db->Consulta($sql);

		$permissoes = array();
		while ( $db->ProximoRegistro() ) {
			$tupla = $db->Tupla();

			$permissoes[] = "{$tupla['cadastra']}, {$tupla['exclui']}, {$tupla['ref_cod_menu_submenu']}";
		}
		
		// Busca os usuarios cadastrados do tipo
		$sql2 = "SELECT cod_usuario FROM pmieducar.usuario WHERE ref_cod_tipo_usuario = $tipo";
		$db->Consulta($sql2);

		
		$cod_usuario = array();
		while ( $db->ProximoRegistro() ) {
			$tupla = $db->Tupla();

			$cod_usuario[] = $tupla['cod_usuario'];
		}


		// Deleta as permissoes existentes e adiciona as novas
		foreach ( $cod_usuario as $cod) {
			$sqlD = "DELETE FROM portal.menu_funcionario WHERE ref_ref_cod_pessoa_fj = $cod";
			$db->Consulta($sqlD);

			$sqlI = "INSERT INTO portal.menu_funcionario VALUES ";
			foreach ( $permissoes as $value) {
				$sqlI .= " ($cod, $value),";
			}

			$sqlI = trim($sqlI, ",");
			$db->Consulta($sqlI);
		}

		return true;
	}

	/**
	 * Cria um novo registro
	 *
	 * @return bool
	 */
	function cadastra()
	{
		if( is_numeric( (int)$this->ref_cod_tipo_usuario ) && is_numeric( $this->ref_cod_menu_submenu ) && is_numeric( (int)$this->cadastra ) && is_numeric( (int)$this->visualiza ) && is_numeric( (int)$this->exclui ) )
		{

			$db = new clsBanco();

			$campos = "";
			$valores = "";
			$gruda = "";

			if( is_numeric( $this->ref_cod_tipo_usuario ) )
			{
				$campos .= "{$gruda}ref_cod_tipo_usuario";
				$valores .= "{$gruda}'{$this->ref_cod_tipo_usuario}'";
				$gruda = ", ";
			}
			if( is_numeric( $this->ref_cod_menu_submenu ) )
			{
				$campos .= "{$gruda}ref_cod_menu_submenu";
				$valores .= "{$gruda}'{$this->ref_cod_menu_submenu}'";
				$gruda = ", ";
			}
			if( is_numeric( $this->cadastra ) )
			{
				$campos .= "{$gruda}cadastra";
				$valores .= "{$gruda}'{$this->cadastra}'";
				$gruda = ", ";
			}
			if( is_numeric( $this->visualiza ) )
			{
				$campos .= "{$gruda}visualiza";
				$valores .= "{$gruda}'{$this->visualiza}'";
				$gruda = ", ";
			}
			if( is_numeric( $this->exclui ) )
			{
				$campos .= "{$gruda}exclui";
				$valores .= "{$gruda}'{$this->exclui}'";
				$gruda = ", ";
			}


			$db->Consulta( "INSERT INTO {$this->_tabela} ( $campos ) VALUES( $valores )" );
			return true;
		}
		return false;
	}

	/**
	 * Edita os dados de um registro
	 *
	 * @return bool
	 */
	function edita()
	{
		if( is_numeric( $this->ref_cod_tipo_usuario ) && is_numeric( $this->ref_cod_menu_submenu ) )
		{

			$db = new clsBanco();
			$set = "";

			if( is_numeric( $this->cadastra ) )
			{
				$set .= "{$gruda}cadastra = '{$this->cadastra}'";
				$gruda = ", ";
			}
			if( is_numeric( $this->visualiza ) )
			{
				$set .= "{$gruda}visualiza = '{$this->visualiza}'";
				$gruda = ", ";
			}
			if( is_numeric( $this->exclui ) )
			{
				$set .= "{$gruda}exclui = '{$this->exclui}'";
				$gruda = ", ";
			}


			if( $set )
			{
				$db->Consulta( "UPDATE {$this->_tabela} SET $set WHERE ref_cod_tipo_usuario = '{$this->ref_cod_tipo_usuario}' AND ref_cod_menu_submenu = '{$this->ref_cod_menu_submenu}'" );

				return true;
			}
		}
		return false;
	}

	/**
	 * Retorna uma lista filtrados de acordo com os parametros
	 *
	 * @return array
	 */
	function lista( $int_ref_cod_tipo_usuario = null, $int_ref_cod_menu_submenu = null, $int_cadastra = null, $int_visualiza = null, $int_exclui = null )
	{
		$sql = "SELECT {$this->_campos_lista} FROM {$this->_tabela}";
		$filtros = "";

		$whereAnd = " WHERE ";
		if( is_numeric( $int_ref_cod_tipo_usuario ) )
		{
			$filtros .= "{$whereAnd} ref_cod_tipo_usuario = '{$int_ref_cod_tipo_usuario}'";
			$whereAnd = " AND ";
		}
		if( is_numeric( $int_ref_cod_menu_submenu ) )
		{
			$filtros .= "{$whereAnd} ref_cod_menu_submenu = '{$int_ref_cod_menu_submenu}'";
			$whereAnd = " AND ";
		}
		if( is_numeric( $int_cadastra ) )
		{
			$filtros .= "{$whereAnd} cadastra = '{$int_cadastra}'";
			$whereAnd = " AND ";
		}
		if( is_numeric( $int_visualiza ) )
		{
			$filtros .= "{$whereAnd} visualiza = '{$int_visualiza}'";
			$whereAnd = " AND ";
		}
		if( is_numeric( $int_exclui ) )
		{
			$filtros .= "{$whereAnd} exclui = '{$int_exclui}'";
			$whereAnd = " AND ";
		}


		$db = new clsBanco();
		$countCampos = count( explode( ",", $this->_campos_lista ) );
		$resultado = array();

		$sql .= $filtros . $this->getOrderby() . $this->getLimite();

		$this->_total = $db->CampoUnico( "SELECT COUNT(0) FROM {$this->_tabela} {$filtros}" );
		$db->Consulta( $sql );
		if( $countCampos > 1 )
		{
			while ( $db->ProximoRegistro() )
			{
				$tupla = $db->Tupla();

				$tupla["_total"] = $this->_total;
				$resultado[] = $tupla;
			}
		}
		else
		{
			while ( $db->ProximoRegistro() )
			{
				$tupla = $db->Tupla();
				$resultado[] = $tupla[$this->_campos_lista];
			}
		}
		if( count( $resultado ) )
		{
			return $resultado;
		}
		return false;
	}

	/**
	 * Retorna um array com os dados de um registro
	 *
	 * @return array
	 */
	function detalhe()
	{
		if( is_numeric( $this->ref_cod_tipo_usuario ) && is_numeric( $this->ref_cod_menu_submenu ) )
		{

		$db = new clsBanco();
		$db->Consulta( "SELECT {$this->_todos_campos} FROM {$this->_tabela} WHERE ref_cod_tipo_usuario = '{$this->ref_cod_tipo_usuario}' AND ref_cod_menu_submenu = '{$this->ref_cod_menu_submenu}'" );
		$db->ProximoRegistro();
		return $db->Tupla();
		}
		return false;
	}

	/**
	 * Retorna um array com os dados de um registro
	 *
	 * @return array
	 */
	function existe()
	{
		if( is_numeric( $this->ref_cod_tipo_usuario ) && is_numeric( $this->ref_cod_menu_submenu ) )
		{

		$db = new clsBanco();
		$db->Consulta( "SELECT 1 FROM {$this->_tabela} WHERE ref_cod_tipo_usuario = '{$this->ref_cod_tipo_usuario}' AND ref_cod_menu_submenu = '{$this->ref_cod_menu_submenu}'" );
		$db->ProximoRegistro();
		return $db->Tupla();
		}
		return false;
	}

	/**
	 * Exclui um registro
	 *
	 * @return bool
	 */
	function excluir()
	{
		if( is_numeric( $this->ref_cod_tipo_usuario ) && is_numeric( $this->ref_cod_menu_submenu ) )
		{

		/*
			delete
		$db = new clsBanco();
		$db->Consulta( "DELETE FROM {$this->_tabela} WHERE ref_cod_tipo_usuario = '{$this->ref_cod_tipo_usuario}' AND ref_cod_menu_submenu = '{$this->ref_cod_menu_submenu}'" );
		return true;
		*/


		}
		return false;
	}

	/**
	 * Define quais campos da tabela serao selecionados na invocacao do metodo lista
	 *
	 * @return null
	 */
	function setCamposLista( $str_campos )
	{
		$this->_campos_lista = $str_campos;
	}

	/**
	 * Define que o metodo Lista devera retornoar todos os campos da tabela
	 *
	 * @return null
	 */
	function resetCamposLista()
	{
		$this->_campos_lista = $this->_todos_campos;
	}

	/**
	 * Define limites de retorno para o metodo lista
	 *
	 * @return null
	 */
	function setLimite( $intLimiteQtd, $intLimiteOffset = null )
	{
		$this->_limite_quantidade = $intLimiteQtd;
		$this->_limite_offset = $intLimiteOffset;
	}

	/**
	 * Retorna a string com o trecho da query resposavel pelo Limite de registros
	 *
	 * @return string
	 */
	function getLimite()
	{
		if( is_numeric( $this->_limite_quantidade ) )
		{
			$retorno = " LIMIT {$this->_limite_quantidade}";
			if( is_numeric( $this->_limite_offset ) )
			{
				$retorno .= " OFFSET {$this->_limite_offset} ";
			}
			return $retorno;
		}
		return "";
	}

	/**
	 * Define campo para ser utilizado como ordenacao no metolo lista
	 *
	 * @return null
	 */
	function setOrderby( $strNomeCampo )
	{
		// limpa a string de possiveis erros (delete, insert, etc)
		//$strNomeCampo = eregi_replace();

		if( is_string( $strNomeCampo ) && $strNomeCampo )
		{
			$this->_campo_order_by = $strNomeCampo;
		}
	}

	/**
	 * Retorna a string com o trecho da query resposavel pela Ordenacao dos registros
	 *
	 * @return string
	 */
	function getOrderby()
	{
		if( is_string( $this->_campo_order_by ) )
		{
			return " ORDER BY {$this->_campo_order_by} ";
		}
		return "";
	}


	/**
	 * Exclui um registro
	 *
	 * @return bool
	 */
	function excluirTudo()
	{
		if( is_numeric( $this->ref_cod_tipo_usuario ) )
		{


		$db = new clsBanco();
		$db->Consulta( "DELETE FROM {$this->_tabela} WHERE ref_cod_tipo_usuario = '{$this->ref_cod_tipo_usuario}'" );
		return true;

		}
		return false;
	}
}
?>
