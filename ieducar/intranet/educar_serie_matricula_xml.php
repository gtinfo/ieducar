<?php

/**
 * i-Educar - Sistema de gest�o escolar
 *
 * Copyright (C) 2006  Prefeitura Municipal de Itaja�
 *                     <ctima@itajai.sc.gov.br>
 *
 * Este programa � software livre; voc� pode redistribu�-lo e/ou modific�-lo
 * sob os termos da Licen�a P�blica Geral GNU conforme publicada pela Free
 * Software Foundation; tanto a vers�o 2 da Licen�a, como (a seu crit�rio)
 * qualquer vers�o posterior.
 *
 * Este programa � distribu��do na expectativa de que seja �til, por�m, SEM
 * NENHUMA GARANTIA; nem mesmo a garantia impl��cita de COMERCIABILIDADE OU
 * ADEQUA��O A UMA FINALIDADE ESPEC�FICA. Consulte a Licen�a P�blica Geral
 * do GNU para mais detalhes.
 *
 * Voc� deve ter recebido uma c�pia da Licen�a P�blica Geral do GNU junto
 * com este programa; se n�o, escreva para a Free Software Foundation, Inc., no
 * endere�o 59 Temple Street, Suite 330, Boston, MA 02111-1307 USA.
 *
 * @author    Prefeitura Municipal de Itaja� <ctima@itajai.sc.gov.br>
 * @category  i-Educar
 * @license   @@license@@
 * @package   iEd_Pmieducar
 * @since     Arquivo dispon�vel desde a vers�o 1.0.0
 * @version   $Id$
 */

header('Content-type: text/xml');

require_once 'include/clsBanco.inc.php';
require_once 'include/funcoes.inc.php';

require_once 'App/Model/MatriculaSituacao.php';

print '<?xml version="1.0" encoding="ISO-8859-1"?>' . "\n";
print '<query xmlns="sugestoes">' . "\n";

@session_start();
$pessoa_logada = $_SESSION['id_pessoa'];
@session_write_close();

/**
 * @param  array  $data
 * @param  string $index
 * @return array  $data[$index] => key($data)
 */
function _createArrayFromIndex(array $data, $index)
{
  $ret = array();
  foreach ($data as $key => $entry) {
    if (isset($entry[$index])) {
      $ret[$entry[$index]] = $key;
    }
  }
  return $ret;
}

/**
 * @param  clsBanco $db
 * @param  string   $sql
 * @return array    (codSerie => nome)
 */
function _getAnoEscolar(clsBanco $db, $sql)
{
  $db->Consulta($sql);

  $resultado = array();
  if ($db->numLinhas()) {
    while ($db->ProximoRegistro()) {
      list($cod, $nome) = $db->Tupla();
      $resultado[$cod] = $nome;
    }
  }

  return $resultado;
}

/**
 * Retorna o ano escolar/s�rie de uma escola.
 *
 * @param  clsBanco $db
 * @param  int      $codSerie  C�digo do ano escolar/s�rie.
 * @return array    (codSerie => nome)
 */
function _mesmoAnoEscolar(clsBanco $db, $codEscola, $codSerie)
{
  $sql = sprintf('SELECT
      s.cod_serie,
      s.nm_serie
    FROM
      pmieducar.serie s,
      pmieducar.escola_serie es
    WHERE
      s.cod_serie = es.ref_cod_serie
      AND es.ref_cod_escola = %d
      AND es.ativo = 1
      AND s.cod_serie = %d
      AND s.ativo = 1
    ORDER BY
      s.nm_serie ASC',
    $codEscola, $codSerie
  );

  return _getAnoEscolar($db, $sql);
}

/**
 * Retorna os anos escolares/s�ries da sequ�ncia de s�rie de uma escola.
 *
 * @param  clsBanco $db
 * @param  int      $codEscola  C�digo da escola.
 * @param  int      $codSerie   C�digo do ano escolar/s�rie.
 * @return array    (codSerie => nome)
 */
function _anoEscolarSequencia(clsBanco $db, $codEscola, $codSerie)
{
  $sql = sprintf('SELECT
      s.cod_serie,
      s.nm_serie
    FROM
      pmieducar.serie s,
      pmieducar.sequencia_serie ss,
      pmieducar.escola_serie es
    WHERE
      ss.ref_serie_destino = s.cod_serie
      AND s.cod_serie = es.ref_cod_serie
      AND es.ref_cod_escola = %d
      AND es.ativo = 1
      AND ss.ref_serie_origem = %d
      AND ss.ativo = 1
    ORDER BY
      s.nm_serie ASC',
    $codEscola, $codSerie
  );

  return _getAnoEscolar($db, $sql);
}

/**
 * Retorna os anos escolares/s�rie do curso de uma escola.
 *
 * @param  clsBanco  $db
 * @param  int       $codEscola  C�digo da escola.
 * @param  int       $codCurso   C�digo do curso.
 * @return array     (codSerie => nome)
 */
function _anoEscolarEscolaCurso(clsBanco $db, $codEscola, $codCurso)
{
  $sql = sprintf('SELECT
      s.cod_serie,
      s.nm_serie
    FROM
      pmieducar.serie s,
      pmieducar.escola_serie es,
      pmieducar.curso c
    WHERE
      es.ref_cod_escola = %d
      AND es.ref_cod_serie = s.cod_serie
      AND es.ativo = 1
      AND s.ref_cod_curso = c.cod_curso
      AND s.ativo = 1
      AND c.cod_curso = %d
    ORDER BY
      s.nm_serie ASC',
    $codEscola, $codCurso
  );

  return _getAnoEscolar($db, $sql);
}

$resultado = array();

if (is_numeric($_GET['alu']) && is_numeric($_GET['ins']) &&
    is_numeric($_GET['cur']) && is_numeric( $_GET['esc'])) {

  $sql = sprintf('SELECT
    m.cod_matricula AS cod_matricula,
    m.ref_ref_cod_escola AS cod_escola,
    m.ref_cod_curso AS cod_curso,
    m.ref_ref_cod_serie AS cod_serie,
    m.ano,
    eal.ano AS ano_letivo,
    c.padrao_ano_escolar,
    m.aprovado,
    COALESCE((
      SELECT
        1
      FROM
        pmieducar.transferencia_solicitacao ts
      WHERE
        m.cod_matricula = ts.ref_cod_matricula_saida
        AND ts.ativo = 1
        AND ts.data_transferencia IS NULL
    ), 0) AS transferencia_int,
    COALESCE((
      SELECT
        1
      FROM
        pmieducar.transferencia_solicitacao ts
      WHERE
        m.cod_matricula = ts.ref_cod_matricula_saida
        AND ts.ativo = 1
        AND ts.data_transferencia IS NOT NULL
        AND ts.ref_cod_matricula_entrada IS NULL
    ), 0) AS transferencia_ext
    FROM
      pmieducar.matricula m,
      pmieducar.escola_ano_letivo eal,
      pmieducar.curso c
    WHERE
      m.ref_cod_aluno = %d
      AND m.ultima_matricula = 1
      AND m.ativo = 1
      AND m.ref_ref_cod_escola = eal.ref_cod_escola
      AND eal.andamento = 1
      AND eal.ativo = 1
      AND m.ref_cod_curso = c.cod_curso
      AND m.aprovado != 6
      AND c.ref_cod_instituicao = %d
    ORDER BY
      m.cod_matricula ASC',
    $_GET['alu'], $_GET['ins']
  );

  $db = new clsBanco();
  $db->Consulta($sql);

  $matriculas = array();
  while ($db->ProximoRegistro()) {
    $tupla = $db->Tupla();
    $matriculas[$tupla['cod_matricula']] = $tupla;
  }

  $codEscola = $_GET['esc'];
  $codCurso  = $_GET['cur'];

  if (count($matriculas)) {
    $cursos = _createArrayFromIndex($matriculas, 'cod_curso');

    // Mesmo curso?
    if (in_array($codCurso, array_keys($cursos))) {
      // Matr�cula do curso.
      $matricula = $matriculas[$cursos[$codCurso]];

      // Matr�cula reprovada, retorna o mesmo ano escolar da matr�cula para a escola selecionada.
      if (App_Model_MatriculaSituacao::REPROVADO == $matricula['aprovado']) {
        $resultado = _mesmoAnoEscolar($db, $codEscola, $matricula['cod_serie']);
      }

      // Matr�cula aprovada, retorna os anos escolares da sequ�ncia de s�rie para a escola selecionada.
      elseif (App_Model_MatriculaSituacao::APROVADO == $matricula['aprovado']) {
        $resultado = _anoEscolarSequencia($db, $codEscola, $matricula['cod_serie']);
      }

      // Matr�cula em andamento
      elseif (App_Model_MatriculaSituacao::EM_ANDAMENTO == $matricula['aprovado']) {
        // Transfer�ncia interna, retorna o mesmo ano escolar da matr�cula para a escola selecionada.
        if (1 == $matricula['transferencia_int']) {
          $resultado = _mesmoAnoEscolar($db, $codEscola, $matricula['cod_serie']);
        }

        // Transfer�ncia externa, retorna os anos escolares da sequ�ncia de s�rie para a escola selecionada.
        elseif (1 == $matricula['transferencia_ext']) {
          $retultado = _anoEscolarSequencia($db, $codEscola, $matricula['cod_serie']);
        }
      }
    }
    else {
      // Retorna todos os anos escolares para o curso em uma escola.
      $resultado = _anoEscolarEscolaCurso($db, $codEscola, $codCurso);
    }
  }
  else {
    $resultado = _anoEscolarEscolaCurso($db, $codEscola, $codCurso);
  }
}

if ($resultado) {
  foreach ($resultado as $cod => $nome) {
    print sprintf('<serie cod_serie="%d">%s</serie>' . "\n", $cod, $nome);
  }
}else{
  print '<serie cod_serie="21">1� Ano</serie>';
  print '<serie cod_serie="23">2� Ano</serie>';
  print '<serie cod_serie="25">3� Ano</serie>';
  print '<serie cod_serie="27">4� Ano</serie>';
  print '<serie cod_serie="29">5� Ano</serie>';
  print '<serie cod_serie="31">6� Ano</serie>';
  print '<serie cod_serie="33">7� Ano</serie>';
  print '<serie cod_serie="35">8� Ano</serie>';
  print '<serie cod_serie="37">9� Ano</serie>';
}

print '</query>';
