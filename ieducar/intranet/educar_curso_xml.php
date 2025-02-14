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
	header( 'Content-type: text/xml' );

	require_once( "include/clsBanco.inc.php" );
	require_once( "include/funcoes.inc.php" );
	echo "<?xml version=\"1.0\" encoding=\"ISO-8859-15\"?>\n<query xmlns=\"sugestoes\">\n";
	if( is_numeric( $_GET["ins"] ) && ( $_GET["sem"] == "true" ) )
	{
		$db = new clsBanco();
		$db->Consulta( "
			SELECT
				cod_curso
				, nm_curso
			FROM
				pmieducar.curso
			WHERE
				ref_cod_instituicao = {$_GET["ins"]}
				AND padrao_ano_escolar = 0
				AND ativo = 1
			ORDER BY
				nm_curso ASC
			" );

		while ( $db->ProximoRegistro() )
		{
			list( $cod, $nome  ) = $db->Tupla();
			echo "	<curso cod_curso=\"{$cod}\">{$nome}</curso>\n";
		}
	}
	elseif( is_numeric( $_GET["ins"] ) )
	{
		$db = new clsBanco();
		$db->Consulta( "SELECT cod_curso, nm_curso,padrao_ano_escolar FROM pmieducar.curso WHERE ref_cod_instituicao = {$_GET["ins"]} AND ativo = 1 ORDER BY nm_curso ASC" );

		// O primeiro da lista ser� a op��o TODOS               
		echo "  <curso cod_curso=\"E\">Todos os cursos</curso>\n";
		while ( $db->ProximoRegistro() )
		{
			list( $cod, $nome,$padrao  ) = $db->Tupla();
			echo "	<curso cod_curso=\"{$cod}\" padrao_ano_escolar=\"{$padrao}\">{$nome}</curso>\n";
		}
	}
	else if( is_numeric( $_GET["esc"] ) )
	{
		$sql_padrao_ano_escolar = "";
		if (is_string($_GET["padrao_ano_escolar"]) && !empty($_GET["padrao_ano_escolar"]))
		{
			if ($_GET["padrao_ano_escolar"] == "nao")
				$sql_padrao_ano_escolar = " AND c.padrao_ano_escolar = 0";
		}
		$db = new clsBanco();
		$db->Consulta( "SELECT 
							c.cod_curso
							, c.nm_curso 
						FROM 
							pmieducar.curso c
							, pmieducar.escola_curso ec 
						WHERE 
							ec.ref_cod_escola = {$_GET["esc"]} 
							AND ec.ref_cod_curso = c.cod_curso 
							AND ec.ativo = 1 
							AND c.ativo = 1
							{$sql_padrao_ano_escolar}
						ORDER BY 
							c.nm_curso ASC" );

		// O primeiro da lista ser� a op��o TODOS		
		echo "  <curso cod_curso=\"E\">Todos os cursos</curso>\n";
		while ( $db->ProximoRegistro() )
		{
			list( $cod, $nome) = $db->Tupla();
			echo "	<curso cod_curso=\"{$cod}\">{$nome}</curso>\n";
		}
	}
	echo "</query>";
?>
