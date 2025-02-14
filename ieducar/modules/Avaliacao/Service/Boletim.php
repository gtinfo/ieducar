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
 * @author      Eriksen Costa Paix�o <eriksen.paixao_bs@cobra.com.br>
 * @category    i-Educar
 * @license     @@license@@
 * @package     Avaliacao
 * @subpackage  Modules
 * @since       Arquivo dispon�vel desde a vers�o 1.1.0
 * @version     $Id$
 */

require_once 'CoreExt/Configurable.php';
require_once 'CoreExt/Entity.php';
require_once 'App/Model/IedFinder.php';
require_once 'App/Model/Matricula.php';
require_once 'App/Model/MatriculaSituacao.php';

/**
 * Avaliacao_Service_Boletim class.
 *
 * Implementa uma API orientada a servi�os (Service Layer Pattern
 * {@link http://martinfowler.com/eaaCatalog/serviceLayer.html}).
 *
 * @author      Eriksen Costa Paix�o <eriksen.paixao_bs@cobra.com.br>
 * @category    i-Educar
 * @license     @@license@@
 * @package     Avaliacao
 * @subpackage  Modules
 * @since       Classe dispon�vel desde a vers�o 1.1.0
 * @todo        Substituir todos os usos literais de 'Rc' e 'An' por constantes
 *              ou por um novo CoreExt_Enum
 * @todo        Criar m�todo que retorna o conjunto de faltas de acordo com o
 *              tipo de presen�a da regra, refatorando a s�rie de condicionais
 *              existentes em m�todos como getSituacaoFaltas()
 * @version     @@package_version@@
 */
class Avaliacao_Service_Boletim implements CoreExt_Configurable
{
  /**
   * Valores escalares.
   * @var array
   */
  protected $_options = array(
    'matricula' => NULL,
    'etapas'    => NULL,
    'usuario'   => NULL
  );

  /**
   * Inst�ncia da regra de avalia��o, com o qual o servi�o ir� utilizar para
   * decidir o fluxo da l�gica.
   * @var RegraAvaliacao_Model_Regra
   */
  protected $_regra = NULL;

  /**
   * @var ComponenteCurricular_Model_ComponenteDataMapper
   */
  protected $_componenteDataMapper = NULL;

  /**
   * @var ComponenteCurricular_Model_TurmaDataMapper
   */
  protected $_componenteTurmaDataMapper = NULL;

  /**
   * @var RegraAvaliacao_Model_RegraDataMapper
   */
  protected $_regraDataMapper = NULL;

  /**
   * @var Avaliacao_Model_NotaAlunoDataMapper
   */
  protected $_notaAlunoDataMapper = NULL;

  /**
   * @var Avaliacao_Model_FaltaAlunoDataMapper
   */
  protected $_faltaAlunoDataMapper = NULL;

  /**
   * @var Avaliacao_Model_ParecerDescritivoAlunoDataMapper
   */
  protected $_parecerDescritivoAlunoDataMapper = NULL;

  /**
   * @var Avaliacao_Model_NotaComponenteDataMapper
   */
  protected $_notaComponenteDataMapper = NULL;

  /**
   * @var Avaliacao_Model_FaltaAbstractDataMapper
   */
  protected $_faltaAbstractDataMapper = NULL;

  /**
   * @var Avaliacao_Model_NotaComponenteMediaDataMapper
   */
  protected $_notaComponenteMediaDataMapper = NULL;

  /**
   * @var Avaliacao_Model_ParecerDescritivoAbstractDataMapper
   */
  protected $_parecerDescritivoAbstractDataMapper = NULL;

  /**
   * Uma inst�ncia de Avaliacao_Model_NotaAluno, que � a entrada que cont�m
   * o cruzamento de matr�cula com as notas do aluno nos diversos componentes
   * cursados.
   *
   * @var Avaliacao_Model_NotaAluno
   */
  protected $_notaAluno = NULL;

  /**
   * Uma inst�ncia de Avaliacao_Model_FaltaAluno, que � a entrada que cont�m
   * o cruzamento de matr�cula com as faltas do aluno nos diversos componentes
   * cursados ou no geral.
   *
   * @var Avaliacao_Model_NotaAluno
   */
  protected $_faltaAluno = NULL;

  /**
   * Uma inst�ncia de Avaliacao_Model_ParecerDescritivoAluno, que � a entrada
   * que cont�m o cruzamento de matr�cula com os pareceres do aluno nos diversos
   * componentes cursados ou no geral.
   *
   * @var Avaliacao_Model_ParecerDescritivoAluno
   */
  protected $_parecerDescritivoAluno = NULL;

  /**
   * Componentes que o aluno cursa, indexado pelo id de
   * ComponenteCurricular_Model_Componente.
   * @var array
   */
  protected $_componentes = NULL;

  /**
   * Notas do aluno nos componentes cursados.
   * @var array
   */
  protected $_notasComponentes = array();

  /**
   * M�dias do aluno nos componentes cursados.
   * @var array
   */
  protected $_mediasComponentes = array();

  /**
   * Notas adicionadas no boletim para inclus�o ou edi��o.
   * @var array
   */
  protected $_notas = array();

  /**
   * Faltas do aluno nos componentes cursados.
   * @var array
   */
  protected $_faltasComponentes = array();

  /**
   * Faltas do aluno no geral.
   * @var array
   */
  protected $_faltasGerais = array();

  /**
   * Faltas adicionadas no boletim para inclus�o ou edi��o.
   * @var array
   */
  protected $_faltas = array();

  /**
   * Pareceres descritivos adicionados no boletim para inclus�o ou edi��o.
   * @var array
   */
  protected $_pareceres = array();

  /**
   * Pareceres descritivos do aluno nos componentes cursados.
   * @var array
   */
  protected $_pareceresComponentes = array();

  /**
   * Pareceres descritivos do aluno no geral.
   * @var array
   */
  protected $_pareceresGerais = array();

  /**
   * Validadores para inst�ncias de Avaliacao_Model_FaltaAbstract e
   * Avaliacao_Model_NotaComponente.
   *
   * @see Avaliacao_Service_Boletim#_addValidators()
   * @var array
   */
  protected $_validators = NULL;

  /**
   * Validadores para uma inst�ncia de Avaliacao_Model_ParecerDescritivoAbstract
   * adicionada no boletim.
   *
   * @see Avaliacao_Service_Boletim#_addParecerValidators()
   * @var array
   */
  protected $_parecerValidators = NULL;

  /**
   * Prioridade da situa��o da matr�cula, usado para definir a situa��o
   * das notas e faltas.
   * @var array
   */
  protected $_situacaoPrioridade = array(
    App_Model_MatriculaSituacao::EM_ANDAMENTO        => 1,
    App_Model_MatriculaSituacao::EM_EXAME            => 2,
    App_Model_MatriculaSituacao::REPROVADO           => 3,
    App_Model_MatriculaSituacao::APROVADO_APOS_EXAME => 4,
    App_Model_MatriculaSituacao::APROVADO            => 5
  );

  /**
   * Construtor.
   *
   * Op��es de configura��o:
   * - matricula (int), obrigat�ria
   * - ComponenteDataMapper (Componente_Model_ComponenteDataMapper), opcional
   * - RegraDataMapper (Regra_Model_RegraDataMapper), opcional
   * - NotaAlunoDataMapper (Avaliacao_Model_NotaAlunoDataMapper), opcional
   *
   * @param array $options
   */
  public function __construct(array $options = array())
  {
    $this->setOptions($options)
         ->_setMatriculaInfo()
         ->_loadNotaComponenteCurricular()
         ->_loadFalta()
         ->_loadParecerDescritivo();
  }

  /**
   * @see CoreExt_Configurable#setOptions()
   */
  public function setOptions(array $options = array())
  {
    if (!isset($options['matricula'])) {
      require_once 'CoreExt/Service/Exception.php';
      throw new CoreExt_Service_Exception('� necess�rio informar o n�mero de '
                . 'matr�cula do aluno.');
    }

    if (isset($options['ComponenteDataMapper'])) {
      $this->setComponenteDataMapper($options['ComponenteDataMapper']);
      unset($options['ComponenteDataMapper']);
    }

    if (isset($options['ComponenteTurmaDataMapper'])) {
      $this->setComponenteTurmaDataMapper($options['ComponenteTurmaDataMapper']);
      unset($options['ComponenteTurmaDataMapper']);
    }

    if (isset($options['RegraDataMapper'])) {
      $this->setRegraDataMapper($options['RegraDataMapper']);
      unset($options['RegraDataMapper']);
    }

    if (isset($options['NotaAlunoDataMapper'])) {
      $this->setNotaAlunoDataMapper($options['NotaAlunoDataMapper']);
      unset($options['NotaAlunoDataMapper']);
    }

    if (isset($options['NotaComponenteDataMapper'])) {
      $this->setNotaComponenteDataMapper($options['NotaComponenteDataMapper']);
      unset($options['NotaComponenteDataMapper']);
    }

    if (isset($options['NotaComponenteMediaDataMapper'])) {
      $this->setNotaComponenteMediaDataMapper($options['NotaComponenteMediaDataMapper']);
      unset($options['NotaComponenteMediaDataMapper']);
    }

    if (isset($options['FaltaAlunoDataMapper'])) {
      $this->setFaltaAlunoDataMapper($options['FaltaAlunoDataMapper']);
      unset($options['FaltaAlunoDataMapper']);
    }

    if (isset($options['FaltaAbstractDataMapper'])) {
      $this->setFaltaAbstractDataMapper($options['FaltaAbstractDataMapper']);
      unset($options['FaltaAbstractDataMapper']);
    }

    if (isset($options['ParecerDescritivoAlunoDataMapper'])) {
      $this->setParecerDescritivoAlunoDataMapper($options['ParecerDescritivoAlunoDataMapper']);
      unset($options['ParecerDescritivoAlunoDataMapper']);
    }

    if (isset($options['ParecerDescritivoAbstractDataMapper'])) {
      $this->setParecerDescritivoAbstractDataMapper($options['ParecerDescritivoAbstractDataMapper']);
      unset($options['ParecerDescritivoAbstractDataMapper']);
    }

    $defaultOptions = array_keys($this->getOptions());
    $passedOptions  = array_keys($options);

    if (0 < count(array_diff($passedOptions, $defaultOptions))) {
      throw new InvalidArgumentException(
        sprintf('A classe %s n�o suporta as op��es: %s.', get_class($this), implode(', ', $passedOptions))
      );
    }

    $this->_options = array_merge($this->getOptions(), $options);

    return $this;
  }

  /**
   * @see CoreExt_Configurable#getOptions()
   */
  public function getOptions()
  {
    return $this->_options;
  }

  /**
   * Setter.
   * @param string $key
   * @param mixed $value
   * @return Avaliacao_Service_Boletim Prov� interface flu�da
   */
  public function setOption($key, $value)
  {
    $this->_options[$key] = $value;
    return $this;
  }

  /**
   * Getter.
   * @param string $key
   * @return mixed
   */
  public function getOption($key)
  {
    return $this->_options[$key];
  }

  /**
   * Setter.
   * @param ComponenteCurricular_Model_ComponenteDataMapper $mapper
   * @return App_Service_Boletim Prov� interface flu�da
   */
  public function setComponenteDataMapper(ComponenteCurricular_Model_ComponenteDataMapper $mapper)
  {
    $this->_componenteDataMapper = $mapper;
    return $this;
  }

  /**
   * Getter.
   * @return ComponenteCurricular_Model_ComponenteDataMapper
   */
  public function getComponenteDataMapper()
  {
    if (is_null($this->_componenteDataMapper)) {
      require_once 'ComponenteCurricular/Model/ComponenteDataMapper.php';
      $this->setComponenteDataMapper(new ComponenteCurricular_Model_ComponenteDataMapper());
    }
    return $this->_componenteDataMapper;
  }

  /**
   * Setter.
   * @param ComponenteCurricular_Model_TurmaDataMapper $mapper
   * @return App_Service_Boletim Prov� interface flu�da
   */
  public function setComponenteTurmaDataMapper(ComponenteCurricular_Model_TurmaDataMapper $mapper)
  {
    $this->_componenteTurmaDataMapper = $mapper;
    return $this;
  }

  /**
   * Getter.
   * @return ComponenteCurricular_Model_TurmaDataMapper
   */
  public function getComponenteTurmaDataMapper()
  {
    if (is_null($this->_componenteTurmaDataMapper)) {
      require_once 'ComponenteCurricular/Model/TurmaDataMapper.php';
      $this->setComponenteTurmaDataMapper(new ComponenteCurricular_Model_TurmaDataMapper());
    }
    return $this->_componenteTurmaDataMapper;
  }

  /**
   * Setter.
   * @param RegraAvaliacao_Model_RegraDataMapper $mapper
   * @return App_Service_Boletim Prov� interface flu�da
   */
  public function setRegraDataMapper(RegraAvaliacao_Model_RegraDataMapper $mapper)
  {
    $this->_regraDataMapper = $mapper;
    return $this;
  }

  /**
   * Getter.
   * @return RegraAvaliacao_Model_RegraDataMapper
   */
  public function getRegraDataMapper()
  {
    if (is_null($this->_regraDataMapper)) {
      require_once 'RegraAvaliacao/Model/RegraDataMapper.php';
      $this->setRegraDataMapper(new RegraAvaliacao_Model_RegraDataMapper());
    }
    return $this->_regraDataMapper;
  }

  /**
   * Setter.
   * @param Avaliacao_Model_NotaAlunoDataMapper $mapper
   * @return App_Service_Boletim Prov� interface flu�da
   */
  public function setNotaAlunoDataMapper(Avaliacao_Model_NotaAlunoDataMapper $mapper)
  {
    $this->_notaAlunoDataMapper = $mapper;
    return $this;
  }

  /**
   * Getter.
   * @return Avaliacao_Model_NotaAlunoDataMapper
   */
  public function getNotaAlunoDataMapper()
  {
    if (is_null($this->_notaAlunoDataMapper)) {
      require_once 'Avaliacao/Model/NotaAlunoDataMapper.php';
      $this->setNotaAlunoDataMapper(new Avaliacao_Model_NotaAlunoDataMapper());
    }
    return $this->_notaAlunoDataMapper;
  }

  /**
   * Setter.
   * @param Avaliacao_Model_NotaComponenteDataMapper $mapper
   * @return App_Service_Boletim Prov� interface flu�da
   */
  public function setNotaComponenteDataMapper(Avaliacao_Model_NotaComponenteDataMapper $mapper)
  {
    $this->_notaComponenteDataMapper = $mapper;
    return $this;
  }

  /**
   * Getter.
   * @return Avaliacao_Model_NotaComponenteDataMapper
   */
  public function getNotaComponenteDataMapper()
  {
    if (is_null($this->_notaComponenteDataMapper)) {
      require_once 'Avaliacao/Model/NotaComponenteDataMapper.php';
      $this->setNotaComponenteDataMapper(new Avaliacao_Model_NotaComponenteDataMapper());
    }
    return $this->_notaComponenteDataMapper;
  }

  /**
   * Setter.
   * @param Avaliacao_Model_NotaMediaComponenteDataMapper $mapper
   * @return App_Service_Boletim Prov� interface flu�da
   */
  public function setNotaComponenteMediaDataMapper(Avaliacao_Model_NotaComponenteMediaDataMapper $mapper)
  {
    $this->_notaComponenteMediaDataMapper = $mapper;
    return $this;
  }

  /**
   * Getter.
   * @return Avaliacao_Model_NotaComponenteMediaDataMapper
   */
  public function getNotaComponenteMediaDataMapper()
  {
    if (is_null($this->_notaComponenteMediaDataMapper)) {
      require_once 'Avaliacao/Model/NotaComponenteMediaDataMapper.php';
      $this->setNotaComponenteMediaDataMapper(new Avaliacao_Model_NotaComponenteMediaDataMapper());
    }
    return $this->_notaComponenteMediaDataMapper;
  }

  /**
   * Setter.
   * @param Avaliacao_Model_FaltaAlunoDataMapper $mapper
   * @return App_Service_Boletim Prov� interface flu�da
   */
  public function setFaltaAlunoDataMapper(Avaliacao_Model_FaltaAlunoDataMapper $mapper)
  {
    $this->_faltaAlunoDataMapper = $mapper;
    return $this;
  }

  /**
   * Getter.
   * @return Avaliacao_Model_NotaAlunoDataMapper
   */
  public function getFaltaAlunoDataMapper()
  {
    if (is_null($this->_faltaAlunoDataMapper)) {
      require_once 'Avaliacao/Model/FaltaAlunoDataMapper.php';
      $this->setFaltaAlunoDataMapper(new Avaliacao_Model_FaltaAlunoDataMapper());
    }
    return $this->_faltaAlunoDataMapper;
  }

  /**
   * Setter.
   * @param Avaliacao_Model_FaltaAbstractDataMapper $mapper
   * @return App_Service_Boletim Prov� interface flu�da
   */
  public function setFaltaAbstractDataMapper(Avaliacao_Model_FaltaAbstractDataMapper $mapper)
  {
    $this->_faltaAbstractDataMapper = $mapper;
    return $this;
  }

  /**
   * Getter.
   * @return Avaliacao_Model_FaltaAbstractDataMapper
   */
  public function getFaltaAbstractDataMapper()
  {
    if (is_null($this->_faltaAbstractDataMapper)) {
      switch ($this->getRegra()->get('tipoPresenca')) {
        case RegraAvaliacao_Model_TipoPresenca::POR_COMPONENTE:
          require_once 'Avaliacao/Model/FaltaComponenteDataMapper.php';
          $class = 'Avaliacao_Model_FaltaComponenteDataMapper';
          break;
        case RegraAvaliacao_Model_TipoPresenca::GERAL:
          require_once 'Avaliacao/Model/FaltaGeralDataMapper.php';
          $class = 'Avaliacao_Model_FaltaGeralDataMapper';
          break;
      }
      $this->setFaltaAbstractDataMapper(new $class());
    }

    return $this->_faltaAbstractDataMapper;
  }

  /**
   * Setter.
   * @param Avaliacao_Model_ParecerDescritivoAlunoDataMapper $mapper
   * @return App_Service_Boletim Prov� interface flu�da
   */
  public function setParecerDescritivoAlunoDataMapper(Avaliacao_Model_ParecerDescritivoAlunoDataMapper $mapper)
  {
    $this->_parecerDescritivoAlunoDataMapper = $mapper;
    return $this;
  }

  /**
   * Getter.
   * @return Avaliacao_Model_ParecerDescritivoAlunoDataMapper
   */
  public function getParecerDescritivoAlunoDataMapper()
  {
    if (is_null($this->_parecerDescritivoAlunoDataMapper)) {
      require_once 'Avaliacao/Model/ParecerDescritivoAlunoDataMapper.php';
      $this->setParecerDescritivoAlunoDataMapper(new Avaliacao_Model_ParecerDescritivoAlunoDataMapper());
    }
    return $this->_parecerDescritivoAlunoDataMapper;
  }

  /**
   * Setter.
   * @param Avaliacao_Model_ParecerDescritivoAbstractDataMapper $mapper
   * @return App_Service_Boletim Prov� interface flu�da
   */
  public function setParecerDescritivoAbstractDataMapper(Avaliacao_Model_ParecerDescritivoAbstractDataMapper $mapper)
  {
    $this->_parecerDescritivoAbstractDataMapper = $mapper;
    return $this;
  }

  /**
   * Getter.
   * @return Avaliacao_Model_ParecerDescritivoAbstractDataMapper
   */
  public function getParecerDescritivoAbstractDataMapper()
  {
    if (is_null($this->_parecerDescritivoAbstractDataMapper)) {
      $parecerDescritivo = $this->getRegra()->get('parecerDescritivo');

      switch($parecerDescritivo) {
        case RegraAvaliacao_Model_TipoParecerDescritivo::ANUAL_GERAL:
        case RegraAvaliacao_Model_TipoParecerDescritivo::ETAPA_GERAL:
          $filename = 'Avaliacao/Model/ParecerDescritivoGeralDataMapper.php';
          $class    = 'Avaliacao_Model_ParecerDescritivoGeralDataMapper';
          break;
        case RegraAvaliacao_Model_TipoParecerDescritivo::ANUAL_COMPONENTE:
        case RegraAvaliacao_Model_TipoParecerDescritivo::ETAPA_COMPONENTE:
          $filename = 'Avaliacao/Model/ParecerDescritivoComponenteDataMapper.php';
          $class    = 'Avaliacao_Model_ParecerDescritivoComponenteDataMapper';
          break;
      }

      // Se n�o usar parecer descritivo, retorna NULL
      if (!isset($filename)) {
        return NULL;
      }

      require_once $filename;
      $this->setParecerDescritivoAbstractDataMapper(new $class());
    }

    return $this->_parecerDescritivoAbstractDataMapper;
  }

  /**
   * Retorna as inst�ncias de Avaliacao_Model_NotaComponente do aluno.
   * @return array
   */
  public function getNotasComponentes()
  {
    return $this->_notasComponentes;
  }

  /**
   * Retorna as inst�ncias de Avaliacao_Model_NotaComponenteMedia do aluno.
   * @return array
   */
  public function getMediasComponentes()
  {
    return $this->_mediasComponentes;
  }

  /**
   * Retorna as inst�ncias de Avaliacao_Model_FaltaComponente do aluno.
   * @return array
   */
  public function getFaltasComponentes()
  {
    return $this->_faltasComponentes;
  }

  /**
   * Retorna as inst�ncias de Avaliacao_Model_FaltaGeral do aluno.
   * @return array
   */
  public function getFaltasGerais()
  {
    return $this->_faltasGerais;
  }

  /**
   * Retorna as inst�ncias de Avaliacao_Model_ParecerDescritivoComponente do
   * aluno.
   * @return array
   */
  public function getPareceresComponentes()
  {
    return $this->_pareceresComponentes;
  }

  /**
   * Retorna as inst�ncias de Avaliacao_Model_ParecerDescritivoGeral do aluno.
   * @return array
   */
  public function getPareceresGerais()
  {
    return $this->_pareceresGerais;
  }

  /**
   * Retorna uma inst�ncia de Avaliacao_Model_NotaComponente.
   *
   * @param int $id O identificador de ComponenteCurricular_Model_Componente
   * @param int $etapa A etapa para o qual a nota foi lan�ada
   * @return Avaliacao_Model_NotaComponente|NULL
   */
  public function getNotaComponente($id, $etapa = 1)
  {
    $componentes = $this->getNotasComponentes();

    if (!isset($componentes[$id])) {
      return NULL;
    }

    $notasComponente = $componentes[$id];

    foreach ($notasComponente as $nota) {
      if ($nota->etapa == $etapa) {
        return $nota;
      }
    }

    return NULL;
  }

  /**
   * Retorna uma inst�ncia de Avaliacao_Model_FaltaAbstract.
   *
   * @param int $etapa A etapa para o qual a falta foi lan�ada
   * @param int $id O identificador de ComponenteCurricular_Model_Componente
   * @return Avaliacao_Model_FaltaAbstract|NULL
   */
  public function getFalta($etapa = 1, $id = NULL)
  {
    if ($this->getRegra()->get('tipoPresenca') == RegraAvaliacao_Model_TipoPresenca::POR_COMPONENTE) {
      $faltas = $this->getFaltasComponentes();

      if (!isset($faltas[$id])) {
        return NULL;
      }

      $faltas = $faltas[$id];
    }
    else {
      $faltas = $this->getFaltasGerais();
    }

    foreach ($faltas as $falta) {
      if ($falta->etapa == $etapa) {
        return $falta;
      }
    }

    return NULL;
  }

  /**
   * Retorna uma inst�ncia de Avaliacao_Model_ParecerDescritivoAbstract.
   *
   * @param int $etapa A etapa para o qual o parecer foi lan�ado
   * @param int $id O identificador de ComponenteCurricular_Model_Componente
   * @return Avaliacao_Model_ParecerAbstract|NULL
   */
  public function getParecerDescritivo($etapa = 1, $id = NULL)
  {
    $parecerDescritivo = $this->getRegra()->get('parecerDescritivo');

    $gerais = array(
      RegraAvaliacao_Model_TipoParecerDescritivo::ANUAL_GERAL,
      RegraAvaliacao_Model_TipoParecerDescritivo::ETAPA_GERAL
    );

    $componentes = array(
      RegraAvaliacao_Model_TipoParecerDescritivo::ANUAL_COMPONENTE,
      RegraAvaliacao_Model_TipoParecerDescritivo::ETAPA_COMPONENTE
    );

    if (in_array($parecerDescritivo, $gerais)) {
      $pareceres = $this->getPareceresGerais();
    }
    elseif (in_array($parecerDescritivo, $componentes)) {
      $pareceres = $this->getPareceresComponentes();

      if (!isset($pareceres[$id])) {
        return NULL;
      }

      $pareceres = $pareceres[$id];
    }

    foreach ($pareceres as $parecer) {
      if ($parecer->etapa == $etapa) {
        return $parecer;
      }
    }

    return NULL;
  }


  /**
   * Setter.
   * @return App_Service_Boletim Prov� interface flu�da
   */
  protected function _setMatriculaInfo()
  {
    $codMatricula = $this->getOption('matricula');

    $this->_setRegra(App_Model_IedFinder::getRegraAvaliacaoPorMatricula(
            $codMatricula, $this->getRegraDataMapper()
           ))

         ->_setComponentes(App_Model_IedFinder::getComponentesPorMatricula(
            $codMatricula, $this->getComponenteDataMapper(),
            $this->getComponenteTurmaDataMapper()
           ));

    // Valores scalar de refer�ncia
    $matricula = App_Model_IedFinder::getMatricula($codMatricula);

    $etapas = App_Model_IedFinder::getQuantidadeDeModulosMatricula($codMatricula);
    $this->setOption('matriculaData', $matricula);
    $this->setOption('aprovado', $matricula['aprovado']);
    $this->setOption('cursoHoraFalta', $matricula['curso_hora_falta']);
    $this->setOption('cursoCargaHoraria', $matricula['curso_carga_horaria']);
    $this->setOption('serieCargaHoraria', $matricula['serie_carga_horaria']);
    $this->setOption('etapas', $etapas);

    return $this;
  }

  /**
   * Carrega todas as notas e m�dias j� lan�adas para a matr�cula atual.
   *
   * @param bool $loadMedias FALSE caso n�o seja necess�rio carregar as m�dias
   * @return App_Service_Boletim Prov� interface flu�da
   */
  protected function _loadNotaComponenteCurricular($loadMedias = TRUE)
  {
    // Cria uma entrada no boletim caso o aluno/matr�cula n�o a tenha
    if (!$this->hasNotaAluno()) {
      $this->_createNotaAluno();
    }

    // Sen�o tiver, vai criar
    $notaAluno = $this->_getNotaAluno();

    $notas = $this->getNotaComponenteDataMapper()->findAll(
      array(), array('notaAluno' => $notaAluno->id), array('etapa' => 'ASC')
    );

    // Separa cada nota em um array indexado pelo identity do componente
    $notasComponentes = array();
    foreach ($notas as $nota) {
      $notasComponentes[$nota->get('componenteCurricular')][] = $nota;
    }

    $this->_notasComponentes = $notasComponentes;

    if (FALSE == $loadMedias) {
      return $this;
    }

    return $this->_loadNotaComponenteCurricularMedia();
  }

  /**
   * Carrega as m�dias dos componentes curriculares j� lan�adas.
   * @return App_Service_Boletim Prov� interface flu�da
   */
  protected function _loadNotaComponenteCurricularMedia()
  {
    $notaAluno = $this->_getNotaAluno();

    $medias = $this->getNotaComponenteMediaDataMapper()->findAll(
      array(), array('notaAluno' => $notaAluno->id)
    );

    $mediasComponentes = array();
    foreach ($medias as $media) {
      $mediasComponentes[$media->get('componenteCurricular')][] = $media;
    }

    $this->_mediasComponentes = $mediasComponentes;

    return $this;
  }

  /**
   * Carrega as faltas do aluno, sejam gerais ou por componente.
   * @return App_Service_Boletim Prov� interface flu�da
   */
  protected function _loadFalta()
  {
    // Cria uma entrada no boletim caso o aluno/matr�cula n�o a tenha
    if (!$this->hasFaltaAluno()) {
      $this->_createFaltaAluno();
    }

    // Sen�o tiver, vai criar
    $faltaAluno = $this->_getFaltaAluno();

    // Carrega as faltas j� lan�adas
    $faltas = $this->getFaltaAbstractDataMapper()->findAll(
      array(), array('faltaAluno' => $faltaAluno->id), array('etapa' => 'ASC')
    );

    // Se a falta for do tipo geral, popula um array indexado pela etapa
    if ($faltaAluno->get('tipoFalta') == RegraAvaliacao_Model_TipoPresenca::GERAL) {
      $faltasGerais = array();

      foreach ($faltas as $falta) {
        $faltasGerais[$falta->etapa] = $falta;
      }

      $this->_faltasGerais = $faltasGerais;
    }
    // Separa cada nota em um array indexado pelo identity field do componente
    elseif ($faltaAluno->get('tipoFalta') == RegraAvaliacao_Model_TipoPresenca::POR_COMPONENTE) {
      $faltasComponentes = array();

      foreach ($faltas as $falta) {
        $faltasComponentes[$falta->get('componenteCurricular')][] = $falta;
      }

      $this->_faltasComponentes = $faltasComponentes;
    }

    return $this;
  }

  /**
   * Carrega os pareceres do aluno, sejam gerais ou por componentes.
   * @return App_Service_Boletim Prov� interface flu�da
   */
  protected function _loadParecerDescritivo()
  {
    if ($this->getRegra()->get('parecerDescritivo') == RegraAvaliacao_Model_TipoParecerDescritivo::NENHUM) {
      return $this;
    }

    if (!$this->hasParecerDescritivoAluno()) {
      $this->_createParecerDescritivoAluno();
    }

    $parecerDescritivoAluno = $this->_getParecerDescritivoAluno();

    $pareceres = $this->getParecerDescritivoAbstractDataMapper()->findAll(
      array(), array('parecerDescritivoAluno' => $parecerDescritivoAluno->id), array('etapa' => 'ASC')
    );

    $gerais = array(
      RegraAvaliacao_Model_TipoParecerDescritivo::ETAPA_GERAL,
      RegraAvaliacao_Model_TipoParecerDescritivo::ANUAL_GERAL
    );

    $componentes = array(
      RegraAvaliacao_Model_TipoParecerDescritivo::ETAPA_COMPONENTE,
      RegraAvaliacao_Model_TipoParecerDescritivo::ANUAL_COMPONENTE
    );

    $parecerDescritivo = $this->getRegra()->get('parecerDescritivo');
    if (in_array($parecerDescritivo, $gerais)) {
      $pareceresGerais = array();

      foreach ($pareceres as $parecer) {
        $pareceresGerais[$parecer->etapa] = $parecer;
      }

      $this->_pareceresGerais = $pareceresGerais;
    }
    elseif (in_array($parecerDescritivo, $componentes)) {
      $pareceresComponentes = array();

      foreach ($pareceres as $parecer) {
        $pareceresComponentes[$parecer->get('componenteCurricular')][] = $parecer;
      }

      $this->_pareceresComponentes = $pareceresComponentes;
    }

    return $this;
  }

  /**
   * Setter.
   * @param RegraAvaliacao_Model_Regra $regra
   * @return App_Service_Boletim Prov� interface flu�da
   */
  protected function _setRegra(RegraAvaliacao_Model_Regra $regra)
  {
    $this->_regra = $regra;
    return $this;
  }

  /**
   * Getter.
   * @return RegraAvaliacao_Model_Regra
   */
  public function getRegra()
  {
    return $this->_regra;
  }

  /**
   * Setter.
   * @param array $componentes
   * @return Avaliacao_Service_Boletim Prov� interface flu�da
   */
  protected function _setComponentes(array $componentes)
  {
    $this->_componentes = $componentes;
    return $this;
  }

  /**
   * Getter.
   * @return array
   */
  public function getComponentes()
  {
    return $this->_componentes;
  }

  /**
   * Getter.
   * @return TabelaArredondamento_Model_Tabela
   */
  public function getTabelaArredondamento()
  {
    return $this->getRegra()->tabelaArredondamento;
  }

  /**
   * Verifica se a regra de avaliaca��o possui recupera��o final.
   * @return bool
   */
  public function hasRecuperacao()
  {
    if (is_null($this->getRegra()->get('formulaRecuperacao'))) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Verifica se o aluno tem notas lan�adas.
   * @return bool
   */
  public function hasNotaAluno()
  {
    if (!is_null($this->_getNotaAluno())) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Retorna a situa��o geral do aluno, levando em considera��o as situa��es
   * das notas (m�dias) e faltas. O retorno � baseado em booleanos, indicando
   * se o aluno est� aprovado, em andamento, em recupera��o ou retido por falta.
   *
   * Retorna tamb�m a situa��o das notas e faltas tais quais retornadas pelos
   * m�todos getSituacaoComponentesCurriculares() e getSituacaoFaltas().
   *
   * <code>
   * <?php
   * $situacao = new stdClass();
   * $situacao->aprovado    = TRUE;
   * $situacao->andamento   = FALSE;
   * $situacao->recuperacao = FALSE;
   * $situacao->retidoFalta = FALSE;
   * $situacao->nota        = $this->getSituacaoComponentesCurriculares();
   * $situacao->falta       = $this->getSituacaoFaltas();
   * </code>
   *
   * @return stdClass
   * @see Avaliacao_Service_Boletim#getSituacaoComponentesCurriculares()
   * @see Avaliacao_Service_Boletim#getSituacaoFaltas()
   */
  public function getSituacaoAluno()
  {
    $situacao = new stdClass();
    $situacao->aprovado    = TRUE;
    $situacao->andamento   = FALSE;
    $situacao->recuperacao = FALSE;
    $situacao->retidoFalta = FALSE;
    $situacao->reprovado   = FALSE;
    $situacao->nota        = NULL;
    $situacao->falta       = NULL;

    $nota  = $this->getSituacaoComponentesCurriculares();
    $falta = $this->getSituacaoFaltas();

    switch ($nota->situacao) {
      case App_Model_MatriculaSituacao::EM_ANDAMENTO:
        $situacao->aprovado  = FALSE;
        $situacao->andamento = TRUE;
        break;
      case App_Model_MatriculaSituacao::APROVADO_APOS_EXAME:
        $situacao->recuperacao = TRUE;
        break;
      case App_Model_MatriculaSituacao::EM_EXAME:
        $situacao->aprovado    = FALSE;
        $situacao->andamento   = TRUE;
        $situacao->recuperacao = TRUE;
        break;
      case App_Model_MatriculaSituacao::REPROVADO:
        $situacao->reprovado   = TRUE;
        $situacao->aprovado    = FALSE;
        break;
    }

    switch ($falta->situacao) {
      case App_Model_MatriculaSituacao::EM_ANDAMENTO:
        $situacao->aprovado  = FALSE;
        $situacao->andamento = TRUE;
        break;
      case App_Model_MatriculaSituacao::REPROVADO:
        $situacao->retidoFalta = TRUE;
        $situacao->aprovado    = FALSE;
        break;
      case App_Model_MatriculaSituacao::APROVADO:
        $situacao->retidoFalta = FALSE;
        break;
    }

    $situacao->nota  = $nota;
    $situacao->falta = $falta;

    return $situacao;
  }

  /**
   * Retorna a situa��o dos componentes curriculares cursados pelo aluno. Possui
   * uma flag "situacao" global, que indica a situa��o global do aluno, podendo
   * ser:
   *
   * - Em andamento
   * - Em exame
   * - Aprovado
   * - Reprovado
   *
   * Esses valores s�o definidos no enum App_Model_MatriculaSituacao.
   *
   * Para cada componente curricular, ser� indicado a situa��o do aluno no
   * componente.
   *
   * Esses resultados s�o retornados como um objeto stdClass que possui dois
   * atributos: "situacao" e "componentesCurriculares". O primeiro � um tipo
   * inteiro e o segundo um array indexado pelo id do componente e com um
   * atributo inteiro "situacao":
   *
   * <code>
   * <?php
   * $situacao = new stdClass();
   * $situacao->situacao = App_Model_MatriculaSituacao::APROVADO;
   * $situacao->componentesCurriculares = array();
   * $situacao->componentesCurriculares[1] = new stdClass();
   * $situacao->componentesCurriculares[1]->situacao = App_Model_MatriculaSituacao::APROVADO;
   * </code>
   *
   * Esses valores s�o definidos atrav�s da verifica��o SOMENTE das m�dias dos
   * componentes curriculares j� avaliados.
   *
   * @return stdClass|NULL Retorna NULL caso n�o
   * @see App_Model_MatriculaSituacao
   */
  public function getSituacaoComponentesCurriculares()
  {
    $situacao = new stdClass();
    $situacao->situacao = 0;
    $situacao->componentesCurriculares = array();

    // A situa��o � "aprovado" por padr�o
    $situacaoGeral = App_Model_MatriculaSituacao::APROVADO;

    if ($this->getRegra()->get('tipoNota') == RegraAvaliacao_Model_Nota_TipoValor::NENHUM) {
      return $situacao;
    }

    // Carrega as m�dias pois este m�todo pode ser chamado ap�s a chamada a
    // saveNotas().
    $mediasComponentes = $this->_loadNotaComponenteCurricularMedia()
                              ->getMediasComponentes();

    // Se n�o tiver nenhuma m�dia ou a quantidade for diferente dos componentes
    // curriculares da matr�cula, ainda est� em andamento
    if (0 == count($mediasComponentes) ||
      count($mediasComponentes) != count($this->getComponentes())) {
      $situacaoGeral = App_Model_MatriculaSituacao::EM_ANDAMENTO;
    }

    foreach ($mediasComponentes as $id => $mediaComponente) {
      $etapa = $mediaComponente[0]->etapa;

      if ($this->getRegra()->get('tipoNota') == RegraAvaliacao_Model_Nota_TipoValor::NUMERICA) {
        $media = $mediaComponente[0]->mediaArredondada;
      }
      else {
        $media = $mediaComponente[0]->media;
      }

      $situacao->componentesCurriculares[$id] = new stdClass();
      $situacao->componentesCurriculares[$id]->situacao = 0;

      if ($etapa == $this->getOption('etapas') && $media < $this->getRegra()->media &&
          $this->hasRecuperacao()) {
        $situacao->componentesCurriculares[$id]->situacao = App_Model_MatriculaSituacao::EM_EXAME;
      }
      elseif (($etapa == $this->getOption('etapas') || $etapa == 'Rc') &&
               $media < $this->getRegra()->media) {
        $situacao->componentesCurriculares[$id]->situacao = App_Model_MatriculaSituacao::REPROVADO;
      }
      elseif ($etapa == 'Rc' && $media >= $this->getRegra()->media && $this->hasRecuperacao()) {
        $situacao->componentesCurriculares[$id]->situacao = App_Model_MatriculaSituacao::APROVADO_APOS_EXAME;
      }
      elseif ($etapa < $this->getOption('etapas') && $etapa != 'Rc') {
        $situacao->componentesCurriculares[$id]->situacao = App_Model_MatriculaSituacao::EM_ANDAMENTO;
      }
      else {
        $situacao->componentesCurriculares[$id]->situacao = App_Model_MatriculaSituacao::APROVADO;
      }

      if ($this->_situacaoPrioritaria($situacao->componentesCurriculares[$id]->situacao,
                                        $situacaoGeral)) {
        $situacaoGeral = $situacao->componentesCurriculares[$id]->situacao;
      }
    }

    // Situa��o geral
    $situacao->situacao = $situacaoGeral;

    return $situacao;
  }

  /**
   * Retorna a situa��o das faltas do aluno, sejam por componentes curriculares
   * ou gerais. A situa��o pode ser:
   *
   * - Em andamento
   * - Aprovado
   * - Reprovado
   *
   * Retorna outros dados interessantes, a maioria informacional para exibi��o
   * ao usu�rio, como a carga hor�ria (geral e por componente), a porcentagem
   * de presen�a (geral e por componente), a porcentagem de falta (geral e
   * por componente), a hora/falta usada para o c�lculo das porcentagens e o
   * total de faltas geral.
   *
   * Esses resultados s�o retornados como um objeto stdClass que possui os
   * seguintes atributos:
   *
   * <code>
   * <?php
   * $presenca = new stdClass();
   * $presenca->situacao                 = 0;
   * $presenca->tipoFalta                = 0;
   * $presenca->cargaHoraria             = 0;
   * $presenca->cursoHoraFalta           = 0;
   * $presenca->totalFaltas              = 0;
   * $presenca->horasFaltas              = 0;
   * $presenca->porcentagemFalta         = 0;
   * $presenca->porcentagemPresenca      = 0;
   * $presenca->porcentagemPresencaRegra = 0;
   *
   * $presenca->componentesCurriculares  = array();
   * $presenca->componentesCurriculares[1] = new stdClass();
   * $presenca->componentesCurriculares[1]->situacao            = 0;
   * $presenca->componentesCurriculares[1]->horasFaltas         = 0;
   * $presenca->componentesCurriculares[1]->porcentagemFalta    = 0;
   * $presenca->componentesCurriculares[1]->porcentagemPresenca = 0;
   * </code>
   *
   * Esses valores s�o calculados SOMENTE atrav�s das faltas j� lan�adas.
   *
   * @return stdClass
   * @todo Verifica��o de situa��o geral nos moldes dos componentes curriculares
   *   para falta por componente (se 0 ou diferente de componentes matr�cula)
   */
  public function getSituacaoFaltas()
  {
    $presenca = new stdClass();
    $presenca->situacao                 = 0;
    $presenca->tipoFalta                = 0;
    $presenca->cargaHoraria             = 0;
    $presenca->cursoHoraFalta           = 0;
    $presenca->totalFaltas              = 0;
    $presenca->horasFaltas              = 0;
    $presenca->porcentagemFalta         = 0;
    $presenca->porcentagemPresenca      = 0;
    $presenca->porcentagemPresencaRegra = 0;
    $presenca->componentesCurriculares  = array();

    // Tipo de falta do aluno
    $tipoFalta = $this->_getFaltaAluno();
    $tipoFalta = $tipoFalta->get('tipoFalta');

    $presenca->tipoFalta                = $tipoFalta;
    $presenca->cursoHoraFalta           = $this->getOption('cursoHoraFalta');
    $presenca->porcentagemPresencaRegra = $this->getRegra()->porcentagemPresenca;
    $presenca->cargaHoraria             = $this->getOption('serieCargaHoraria');

    // A situa��o � "aprovado" por padr�o
    $situacaoGeral = App_Model_MatriculaSituacao::APROVADO;

    // Faltas
    $faltasComponentes = array();

    // Etapa padr�o
    $etapa = 0;

    // Faltas lan�adas (persistidas)
    $this->_loadFalta();

    if ($presenca->tipoFalta == RegraAvaliacao_Model_TipoPresenca::GERAL) {
      $faltas = $this->_faltasGerais;

      if (0 == count($faltas)) {
        $total = 0;
        $etapa = 0;
      }
      else {
        $total  = array_sum(CoreExt_Entity::entityFilterAttr($faltas, 'id', 'quantidade'));
        $falta  = (array_pop($faltas));
        $etapa  = $falta->etapa;
      }
    }
    elseif ($presenca->tipoFalta == RegraAvaliacao_Model_TipoPresenca::POR_COMPONENTE) {
      $faltas = $this->_faltasComponentes;
      $total   = 0;
      $etapasComponentes = array();
      $faltasComponentes = array();

      foreach ($faltas as $key => $falta) {
        // Total de faltas do componente
        $componenteTotal = array_sum(CoreExt_Entity::entityFilterAttr($falta,
          'id', 'quantidade'));

        // Pega o id de ComponenteCurricular_Model_Componente da �ltima etapa do array
        $componenteEtapa = array_pop($falta);
        $id = $componenteEtapa->get('componenteCurricular');
        $etapa = $componenteEtapa->etapa;

        // Etapas lan�adas
        $etapasComponentes[$etapa] = $etapa;

        // Usa stdClass como interface de acesso
        $faltasComponentes[$id] = new stdClass();
        $faltasComponentes[$id]->situacao = App_Model_MatriculaSituacao::EM_ANDAMENTO;
        $faltasComponentes[$id]->horasFaltas = NULL;
        $faltasComponentes[$id]->porcentagemFalta = NULL;
        $faltasComponentes[$id]->porcentagemPresenca = NULL;

        // Calcula a quantidade de horas/faltas no componente
        $faltasComponentes[$id]->horasFaltas =
          $this->_calculateHoraFalta($componenteTotal, $presenca->cursoHoraFalta);

        // Calcula a porcentagem de falta no componente
        $faltasComponentes[$id]->porcentagemFalta =
          $this->_calculatePorcentagem($this->_componentes[$id]->cargaHoraria,
            $faltasComponentes[$id]->horasFaltas, FALSE);

        // Calcula a porcentagem de presen�a no componente
        $faltasComponentes[$id]->porcentagemPresenca =
          100 - $faltasComponentes[$id]->porcentagemFalta;

        if ($etapa == $this->getOption('etapas') || $etapa == 'Rc') {
          $aprovado = ($faltasComponentes[$id]->porcentagemPresenca >= $this->getRegra()->porcentagemPresenca);
          $faltasComponentes[$id]->situacao = $aprovado ?
            App_Model_MatriculaSituacao::APROVADO : App_Model_MatriculaSituacao::REPROVADO;
        }

        // Adiciona a quantidade de falta do componente ao total geral de faltas
        $total += $componenteTotal;
      }

      if (0 == count($faltasComponentes) ||
          count($faltasComponentes) != count($this->getComponentes())) {
        $etapa = 1;
      }
      else {
        $etapa = min($etapasComponentes);
      }
    }

    // Total de faltas
    $presenca->totalFaltas = $total;

    // Horas faltas total
    $presenca->horasFaltas = $this->_calculateHoraFalta($total, $presenca->cursoHoraFalta);

    // Porcentagem de faltas
    $presenca->porcentagemFalta = $this->_calculatePorcentagem($presenca->cargaHoraria,
        $presenca->horasFaltas, FALSE);

    // Porcentagem de presen�a geral
    $presenca->porcentagemPresenca = 100 - $presenca->porcentagemFalta;

    // Componentes curriculares
    $presenca->componentesCurriculares = $faltasComponentes;

    // Situa��o geral. �ltima verifica��o pois somente ao fim � poss�vel saber
    // se est� em andamento ou se pode estabelecer como aprovado ou reprovado
    if ($etapa == $this->getOption('etapas') || $etapa === 'Rc') {
      $aprovado = ($presenca->porcentagemPresenca >= $this->getRegra()->porcentagemPresenca);
      $presenca->situacao = $aprovado ?
        App_Model_MatriculaSituacao::APROVADO :
        App_Model_MatriculaSituacao::REPROVADO;
    }
    else {
      $presenca->situacao = App_Model_MatriculaSituacao::EM_ANDAMENTO;
    }

    return $presenca;
  }

  /**
   * Verifica se uma determinada situa��o tem prioridade sobre a outra.
   *
   * @param int $item1
   * @param int $item2
   * @return bool
   */
  protected function _situacaoPrioritaria($item1, $item2)
  {
    return ($this->_situacaoPrioridade[$item1] <= $this->_situacaoPrioridade[$item2]);
  }

  /**
   * Setter.
   * @param Avaliacao_Model_NotaAluno $nota
   * @return Avaliacao_Service_Boletim Prov� interface flu�da
   */
  protected function _setNotaAluno(Avaliacao_Model_NotaAluno $nota)
  {
    $this->_notaAluno = $nota;
    return $this;
  }

  /**
   * Getter.
   * @return Avaliacao_Model_NotaAluno|NULL
   */
  protected function _getNotaAluno()
  {
    if (!is_null($this->_notaAluno)) {
      return $this->_notaAluno;
    }

    $notaAluno = $this->getNotaAlunoDataMapper()->findAll(
      array(),
      array('matricula' => $this->getOption('matricula'))
    );

    if (0 == count($notaAluno)) {
      return NULL;
    }

    $this->_setNotaAluno($notaAluno[0]);
    return $this->_notaAluno;
  }

  /**
   * Cria e persiste uma inst�ncia de Avaliacao_Model_NotaAluno.
   * @return bool
   */
  protected function _createNotaAluno()
  {
    $notaAluno = new Avaliacao_Model_NotaAluno();
    $notaAluno->matricula = $this->getOption('matricula');
    return $this->getNotaAlunoDataMapper()->save($notaAluno);
  }

  /**
   * Verifica se existe alguma inst�ncia de Avaliacao_Model_NotaComponente para
   * um determinado componente curricular j� persistida.
   *
   * @param int $id
   * @return bool
   */
  protected function _hasNotaComponente($id)
  {
    $notasComponentes = $this->getNotasComponentes();
    if (!isset($notasComponentes[$id])) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Retorna o field identity de um componente curricular de uma inst�ncia de
   * Avaliacao_Model_NotaComponente j� esteja persistida.
   *
   * @param Avaliacao_Model_NotaComponente $instance
   * @return int|NULL Retorna NULL caso a inst�ncia n�o tenha sido lan�ada
   */
  protected function _getNotaIdEtapa(Avaliacao_Model_NotaComponente $instance)
  {
    $componenteCurricular = $instance->get('componenteCurricular');

    if (!$this->_hasNotaComponente($componenteCurricular)) {
      return NULL;
    }

    $notasComponentes = $this->getNotasComponentes();
    foreach ($notasComponentes[$componenteCurricular] as $notaComponente) {
      if ($instance->etapa == $notaComponente->etapa) {
        return $notaComponente->id;
      }
    }

    return NULL;
  }

  /**
   * Verifica se o aluno tem faltas lan�adas.
   * @return bool
   */
  public function hasFaltaAluno()
  {
    if (!is_null($this->_getFaltaAluno())) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Setter.
   * @param Avaliacao_Model_FaltaAluno $falta
   * @return Avaliacao_Service_Boletim Prov� interface flu�da
   */
  protected function _setFaltaAluno(Avaliacao_Model_FaltaAluno $falta)
  {
    $this->_faltaAluno = $falta;
    return $this;
  }

  /**
   * Getter.
   * @return Avaliacao_Model_FaltaAluno|NULL
   */
  protected function _getFaltaAluno()
  {
    if (!is_null($this->_faltaAluno)) {
      return $this->_faltaAluno;
    }

    $faltaAluno = $this->getFaltaAlunoDataMapper()->findAll(
      array(),
      array('matricula' => $this->getOption('matricula'))
    );

    if (0 == count($faltaAluno)) {
      return NULL;
    }

    $this->_setFaltaAluno($faltaAluno[0]);
    return $this->_faltaAluno;
  }

  /**
   * Cria e persiste uma inst�ncia de Avaliacao_Model_NotaAluno.
   * @return bool
   */
  protected function _createFaltaAluno()
  {
    $faltaAluno = new Avaliacao_Model_FaltaAluno();
    $faltaAluno->matricula = $this->getOption('matricula');
    $faltaAluno->tipoFalta = $this->getRegra()->get('tipoPresenca');
    return $this->getFaltaAlunoDataMapper()->save($faltaAluno);
  }

  /**
   * Verifica se existe alguma inst�ncia de Avaliacao_Model_FaltaGeral j�
   * persistida.
   *
   * @return bool
   */
  protected function _hasFaltaGeral()
  {
    $faltasGerais = $this->getFaltasGerais();
    if (0 == count($faltasGerais)) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Verifica se existe alguma inst�ncia de Avaliacao_Model_FaltaComponente para
   * um determinado componente curricular j� persistida.
   *
   * @param int $id
   * @return bool
   */
  protected function _hasFaltaComponente($id)
  {
    $faltasComponentes = $this->getFaltasComponentes();
    if (!isset($faltasComponentes[$id])) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Verifica se existe alguma inst�ncia de Avaliacao_Model_FaltaAbstract j�
   * persistida em uma determinada etapa e retorna o field identity.
   *
   * @param Avaliacao_Model_FaltaAbstract $instance
   * @return int|NULL
   */
  protected function _getFaltaIdEtapa(Avaliacao_Model_FaltaAbstract $instance)
  {
    $etapa = $instance->etapa;

    if (!is_null($instance) &&
      $this->_getFaltaAluno()->get('tipoFalta') == RegraAvaliacao_Model_TipoPresenca::POR_COMPONENTE) {

      $componenteCurricular = $instance->get('componenteCurricular');

      if (!$this->_hasFaltaComponente($componenteCurricular)) {
        return NULL;
      }

      $faltasComponentes = $this->getFaltasComponentes();
      foreach ($faltasComponentes[$componenteCurricular] as $faltaComponente) {
        if ($etapa == $faltaComponente->etapa) {
          return $faltaComponente->id;
        }
      }
    }
    elseif ($this->_getFaltaAluno()->get('tipoFalta') == RegraAvaliacao_Model_TipoPresenca::GERAL) {
      if (!$this->_hasFaltaGeral()) {
        return NULL;
      }

      $faltasGerais = $this->getFaltasGerais();
      if (isset($faltasGerais[$etapa])) {
        return $faltasGerais[$etapa]->id;
      }
    }

    return NULL;
  }

  /**
   * Verifica se o aluno tem pareceres lan�ados.
   * @return bool
   */
  public function hasParecerDescritivoAluno()
  {
    if (!is_null($this->_getParecerDescritivoAluno())) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Setter.
   * @param Avaliacao_Model_ParecerDescritivoAluno $parecerDescritivoAluno
   * @return Avaliacao_Service_Boletim Prov� interface flu�da
   */
  protected function _setParecerDescritivoAluno(Avaliacao_Model_ParecerDescritivoAluno $parecerDescritivoAluno)
  {
    $this->_parecerDescritivoAluno = $parecerDescritivoAluno;
    return $this;
  }

  /**
   * Getter.
   * @return Avaliacao_Model_ParecerDescritivoAluno|NULL
   */
  protected function _getParecerDescritivoAluno()
  {
    if (!is_null($this->_parecerDescritivoAluno)) {
      return $this->_parecerDescritivoAluno;
    }

    $parecerDescritivoAluno = $this->getParecerDescritivoAlunoDataMapper()->findAll(
      array(), array('matricula' => $this->getOption('matricula'))
    );

    if (0 == count($parecerDescritivoAluno)) {
      return NULL;
    }

    $this->_setParecerDescritivoAluno($parecerDescritivoAluno[0]);
    return $this->_parecerDescritivoAluno;
  }

  /**
   * Cria e persiste uma inst�ncia de Avaliacao_Model_ParecerDescritivoAluno.
   * @return bool
   */
  protected function _createParecerDescritivoAluno()
  {
    $parecerDescritivoAluno = new Avaliacao_Model_ParecerDescritivoAluno();
    $parecerDescritivoAluno->matricula         = $this->getOption('matricula');
    $parecerDescritivoAluno->parecerDescritivo = $this->getRegra()->get('parecerDescritivo');
    return $this->getParecerDescritivoAlunoDataMapper()->save($parecerDescritivoAluno);
  }

  /**
   * Adiciona um array de inst�ncias Avaliacao_Model_NotaComponente.
   *
   * @param array $notas
   * @return Avaliacao_Service_Boletim Prov� interface flu�da
   */
  public function addNotas(array $notas)
  {
    foreach ($notas as $nota) {
      $this->addNota($nota);
    }
    return $this;
  }

  /**
   * Verifica se existe alguma inst�ncia de Avaliacao_Model_ParecerDescritivoComponente
   * persistida para o aluno.
   *
   * @param int $id Field identity de ComponenteCurricular_Model_Componente
   * @return bool
   */
  protected function _hasParecerComponente($id)
  {
    $pareceresComponentes = $this->getPareceresComponentes();
    if (!isset($pareceresComponentes[$id])) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Verifica se existe alguma inst�ncia de Avaliacao_Model_ParecerDescritivoGeral
   * persistida para o aluno.
   * @return bool
   */
  protected function _hasParecerGeral()
  {
    if (0 == count($this->getPareceresGerais())) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Verifica se existe alguma inst�ncia de Avaliacao_Model_ParecerDescritivoAbstract
   * persistida em uma determinada etapa e retorna o field identity.
   *
   * @param Avaliacao_Model_ParecerDescritivoAbstract $instance
   * @return int|NULL
   */
  protected function _getParecerIdEtapa(Avaliacao_Model_ParecerDescritivoAbstract $instance)
  {
    $gerais = array(
      RegraAvaliacao_Model_TipoParecerDescritivo::ANUAL_GERAL,
      RegraAvaliacao_Model_TipoParecerDescritivo::ETAPA_GERAL
    );

    $componentes = array(
      RegraAvaliacao_Model_TipoParecerDescritivo::ANUAL_COMPONENTE,
      RegraAvaliacao_Model_TipoParecerDescritivo::ETAPA_COMPONENTE
    );

    $parecerDescritivo = $this->getRegra()->get('parecerDescritivo');

    if (in_array($parecerDescritivo, $gerais)) {
      if (!$this->_hasParecerGeral()) {
        return NULL;
      }

      $pareceres = $this->getPareceresGerais();
    }
    elseif (in_array($parecerDescritivo, $componentes)) {
      if (!$this->_hasParecerComponente($instance->get('componenteCurricular'))) {
        return NULL;
      }

      $pareceres = $this->getPareceresComponentes();
      $pareceres = $pareceres[$instance->get('componenteCurricular')];
    }

    foreach ($pareceres as $parecer) {
      if ($instance->etapa == $parecer->etapa) {
        return $parecer->id;
      }
    }
  }

  /**
   * Adiciona notas no boletim.
   * @param Avaliacao_Model_NotaComponente $nota
   * @return Avaliacao_Service_Boletim Prov� interface flu�da
   */
  public function addNota(Avaliacao_Model_NotaComponente $nota)
  {
    $key = 'n_' . spl_object_hash($nota);

    $nota = $this->_addValidators($nota);
    $nota = $this->_updateEtapa($nota);

    $nota->notaArredondada = $this->arredondaNota($nota);
    $this->_notas[$key]    = $nota;

    return $this;
  }

  /**
   * Getter.
   * @return array
   */
  public function getNotas()
  {
    return $this->_notas;
  }

  /**
   * Adiciona um array de inst�ncias Avaliacao_Model_FaltaAbstract no boletim.
   *
   * @param array $faltas
   * @return Avaliacao_Service_Boletim Prov� interface flu�da
   */
  public function addFaltas(array $faltas)
  {
    foreach ($faltas as $falta) {
      $this->addFalta($falta);
    }
    return $this;
  }

  /**
   * Adiciona faltas no boletim.
   * @param Avaliacao_Model_FaltaAbstract $falta
   * @return Avaliacao_Service_Boletim Prov� interface flu�da
   */
  public function addFalta(Avaliacao_Model_FaltaAbstract $falta)
  {
    $key = 'f_' . spl_object_hash($falta);

    $falta = $this->_addValidators($falta);
    $falta = $this->_updateEtapa($falta);

    $this->_faltas[$key] = $falta;
    return $this;
  }

  /**
   * Getter.
   * @return array
   */
  public function getFaltas()
  {
    return $this->_faltas;
  }

  /**
   * Adiciona uma array de inst�ncias de Avaliacao_Model_ParecerDescritivoAbstract
   * no boletim.
   *
   * @param array $pareceres
   * @return Avaliacao_Service_Boletim Prov� interface flu�da
   */
  public function addPareceres(array $pareceres)
  {
    foreach ($pareceres as $parecer) {
      $this->addParecer($parecer);
    }
    return $this;
  }

  /**
   * Adiciona uma inst�ncia de Avaliacao_Model_ParecerDescritivoAbstract no
   * boletim.
   *
   * @param Avaliacao_Model_ParecerDescritivoAbstract $parecer
   * @return Avaliacao_Service_Boletim Prov� interface flu�da
   */
  public function addParecer(Avaliacao_Model_ParecerDescritivoAbstract $parecer)
  {
    $key = 'p_' . spl_object_hash($parecer);

    $this->_pareceres[$key] = $parecer;
    $this->_updateParecerEtapa($parecer);
    $this->_addParecerValidators($parecer);

    return $this;
  }

  /**
   * Getter para as inst�ncias de Avaliacao_Model_ParecerDescritivoAbstract
   * adicionadas no boletim (n�o persistidas).
   *
   * @return array
   */
  public function getPareceres()
  {
    return $this->_pareceres;
  }

  /**
   * Atualiza as op��es de valida��o de uma inst�ncia de
   * CoreExt_Validate_Validatable, com os valores permitidos para os atributos
   * 'componenteCurricular' e 'etapa'.
   *
   * @param CoreExt_Validate_Validatable $nota
   * @return CoreExt_Validate_Validatable
   * @todo Substituir vari�vel est�tica por uma de inst�ncia {@see _updateParecerEtapa()}
   */
  protected function _addValidators(CoreExt_Validate_Validatable $validatable)
  {
    $validators = array();

    // Como os componentes ser�o os mesmos, fazemos cache do validador
    if (is_null($this->_validators)) {

      $componentes = $this->getComponentes();
      $componentes = CoreExt_Entity::entityFilterAttr($componentes, 'id', 'id');

      // S� pode adicionar uma nota/falta para os componentes cursados
      $validators['componenteCurricular'] = new CoreExt_Validate_Choice(
        array('choices' => $componentes
      ));

      // Pode informar uma nota para as etapas
      $etapas = $this->getOption('etapas');
      $etapas = array_merge(range(1, $etapas, 1), array('Rc'));

      $validators['etapa'] = new CoreExt_Validate_Choice(
        array('choices' => $etapas
      ));

      $this->_validators = $validators;
    }

    $validators = $this->_validators;

    if ($this->getRegra()->get('tipoPresenca') == RegraAvaliacao_Model_TipoPresenca::POR_COMPONENTE) {
      $validatable->setValidator('componenteCurricular', $validators['componenteCurricular']);
    }
    $validatable->setValidator('etapa', $validators['etapa']);

    return $validatable;
  }

  /**
   * Atualiza as op��es de valida��o de uma inst�ncia de
   * Avaliacao_Model_ParecerDescritivoAbstract, com os valores permitidos
   * para os atributos 'componenteCurricular' e 'etapa'.
   *
   * @param Avaliacao_Model_ParecerDescritivoAbstract $instance
   * @return Avaliacao_Model_ParecerDescritivoAbstract
   */
  protected function _addParecerValidators(Avaliacao_Model_ParecerDescritivoAbstract $instance)
  {
    if (is_null($this->_parecerValidators)) {
      $validators = array();

      $anuais = array(
        RegraAvaliacao_Model_TipoParecerDescritivo::ANUAL_GERAL,
        RegraAvaliacao_Model_TipoParecerDescritivo::ANUAL_COMPONENTE
      );

      $etapas = array(
        RegraAvaliacao_Model_TipoParecerDescritivo::ETAPA_GERAL,
        RegraAvaliacao_Model_TipoParecerDescritivo::ETAPA_COMPONENTE
      );

      $parecerDescritivo = $this->getRegra()->get('parecerDescritivo');

      if (in_array($parecerDescritivo, $anuais)) {
        $validators['etapa'] = new CoreExt_Validate_Choice(array(
          'choices' => array('An')
        ));
      }
      elseif (in_array($parecerDescritivo, $etapas)) {
        $etapas = $this->getOption('etapas');
        $etapas = array_merge(range(1, $etapas, 1), array('Rc'));

        $validators['etapa'] = new CoreExt_Validate_Choice(array(
          'choices' => $etapas
        ));
      }

      if ($instance instanceof Avaliacao_Model_ParecerDescritivoComponente) {
        $componentes = $this->getComponentes();
        $componentes = CoreExt_Entity::entityFilterAttr($componentes, 'id', 'id');

        $validators['componenteCurricular'] = new CoreExt_Validate_Choice(array(
          'choices' => $componentes
        ));
      }

      // Armazena os validadores na inst�ncia
      $this->_parecerValidators = $validators;
    }

    $validators = $this->_parecerValidators;

    // Etapas
    $instance->setValidator('etapa', $validators['etapa']);

    // Componentes curriculares
    if ($instance instanceof Avaliacao_Model_ParecerDescritivoComponente) {
      $instance->setValidator('componenteCurricular', $validators['componenteCurricular']);
    }

    return $instance;
  }

  /**
   * Atualiza a etapa de uma inst�ncia de Avaliacao_Model_Etapa.
   *
   * @param Avaliacao_Model_Etapa $nota
   * @return Avaliacao_Model_Etapa
   */
  protected function _updateEtapa(Avaliacao_Model_Etapa $instance)
  {
    if (!is_null($instance->etapa) && $instance->isValid('etapa')) {
      return $instance;
    }

    $proximaEtapa = 1;

    // Se for falta e do tipo geral, verifica qual foi a �ltima etapa
    if ($instance instanceof Avaliacao_Model_FaltaGeral) {
      if (0 < count($this->_faltasGerais)) {
        $etapas = CoreExt_Entity::entityFilterAttr($this->_faltasGerais, 'id', 'etapa');
        $proximaEtapa = max($etapas) + 1;
      }
    }
    // Se for nota ou falta por componente, verifica no conjunto qual a �ltima etapa
    else {
      if ($instance instanceof Avaliacao_Model_NotaComponente) {
        $search = '_notasComponentes';
      }
      elseif ($instance instanceof Avaliacao_Model_FaltaComponente) {
        $search = '_faltasComponentes';
      }

      if (isset($this->{$search}[$instance->get('componenteCurricular')])) {
        $etapas = CoreExt_Entity::entityFilterAttr(
          $this->{$search}[$instance->get('componenteCurricular')], 'id', 'etapa'
        );

        $proximaEtapa = max($etapas) + 1;
      }
    }

    // Se ainda estiver dentro dos limites, ok
    if ($proximaEtapa <= $this->getOption('etapas')) {
      $instance->etapa = $proximaEtapa;
    }
    // Se for maior, verifica se tem recupera��o e atribui etapa como 'Rc'
    elseif ($proximaEtapa > $this->getOption('etapas') &&
      $this->hasRecuperacao()) {
      $instance->etapa = 'Rc';
    }

    return $instance;
  }

  /**
   * Atualiza a etapa de uma inst�ncia de Avaliacao_Model_ParecerDescritivoAbstract
   * para a �ltima etapa poss�vel.
   *
   * @param Avaliacao_Model_ParecerDescritivoAbstract $instance
   * @return Avaliacao_Model_ParecerDescritivoAbstract
   */
  protected function _updateParecerEtapa(Avaliacao_Model_ParecerDescritivoAbstract $instance)
  {
    if (!is_null($instance->etapa)) {
      return $instance;
    }

    $proximaEtapa = 1;

    $anuais = array(
      RegraAvaliacao_Model_TipoParecerDescritivo::ANUAL_GERAL,
      RegraAvaliacao_Model_TipoParecerDescritivo::ANUAL_COMPONENTE
    );

    $etapas = array(
      RegraAvaliacao_Model_TipoParecerDescritivo::ETAPA_GERAL,
      RegraAvaliacao_Model_TipoParecerDescritivo::ETAPA_COMPONENTE
    );

    $componentes = array(
      RegraAvaliacao_Model_TipoParecerDescritivo::ANUAL_COMPONENTE,
      RegraAvaliacao_Model_TipoParecerDescritivo::ETAPA_COMPONENTE
    );

    $gerais = array(
      RegraAvaliacao_Model_TipoParecerDescritivo::ANUAL_GERAL,
      RegraAvaliacao_Model_TipoParecerDescritivo::ETAPA_GERAL
    );

    $parecerDescritivo = $this->getRegra()->get('parecerDescritivo');
    if (in_array($parecerDescritivo, $anuais)) {
      $instance->etapa = 'An';
      return $instance;
    }
    elseif (in_array($parecerDescritivo, $etapas)) {
      $attrValues = array();

      if (in_array($parecerDescritivo, $gerais)) {
        $attrValues = $this->getPareceresGerais();
      }
      elseif (in_array($parecerDescritivo, $componentes)) {
        $pareceresComponentes = $this->getPareceresComponentes();
        if (isset($pareceresComponentes[$instance->get('componenteCurricular')])) {
          $attrValues = $pareceresComponentes[$instance->get('componenteCurricular')];
        }
      }

      if (0 < count($attrValues)) {
        $etapas = CoreExt_Entity::entityFilterAttr($attrValues, 'id', 'etapa');
        $proximaEtapa = max($etapas) + 1;
      }
    }

    if ($proximaEtapa <= $this->getOption('etapas')) {
      $instance->etapa = $proximaEtapa;
    }
    elseif ($this->hasRecuperacao()) {
      $instance->etapa = 'Rc';
    }

    return $instance;
  }

  /**
   * Arredonda uma nota atrav�s da tabela de arredondamento da regra de avalia��o.
   * @param Avaliacao_Model_NotaComponente|int $nota
   * @return mixed
   * @throws CoreExt_Exception_InvalidArgumentException
   */
  public function arredondaNota($nota)
  {
    if ($nota instanceof Avaliacao_Model_NotaComponente) {
      $nota = $nota->nota;
    }

    if (!is_numeric($nota)) {
      require_once 'CoreExt/Exception/InvalidArgumentException.php';
      throw new CoreExt_Exception_InvalidArgumentException(sprintf(
        'O par�metro $nota ("%s") n�o � um valor num�rico.', $nota
      ));
    }

    return $this->getRegra()->tabelaArredondamento->round($nota);
  }

  /**
   * Prev� a nota necess�ria para que o aluno seja aprovado ap�s a recupera��o
   * escolar.
   *
   * @param  int $id
   * @return TabelaArredondamento_Model_TabelaValor|NULL
   * @see    TabelaArredondamento_Model_Tabela#predictValue()
   */
  public function preverNotaRecuperacao($id)
  {
    if (is_null($this->getRegra()->formulaRecuperacao) || !isset($this->_notasComponentes[$id])) {
      return NULL;
    }

    $notas      = $this->_notasComponentes[$id];
    $somaEtapas = array_sum(CoreExt_Entity::entityFilterAttr($notas, 'etapa', 'nota'));
    $formula    = $this->getRegra()->formulaRecuperacao;

    $data = array(
      'formulaValues' => array(
        'Se' => $somaEtapas,
        'Et' => $this->getOption('etapas'),
        'Rc' => NULL
      ),
      'expected' => array(
        'var'   => 'Rc',
        'value' => $this->getRegra()->media
      )
    );

    foreach ($notas as $nota) {
      $data['formulaValues']['E' . $nota->etapa] = $nota->nota;
    }

    return $this->getRegra()->tabelaArredondamento->predictValue($formula, $data);
  }

  /**
   * @param  numeric $falta
   * @param  numeric $horaFalta
   * @return numeric
   */
  protected function _calculateHoraFalta($falta, $horaFalta)
  {
    return ($falta * $horaFalta);
  }

  /**
   * Calcula a propor��o de $num2 para $num1.
   *
   * @param  numeric $num1
   * @param  numeric $num2
   * @param  bool    $decimal Opcional. Se o resultado � retornado como decimal
   *   ou percentual. O padr�o � TRUE.
   * @return float
   */
  protected function _calculatePorcentagem($num1, $num2, $decimal = TRUE)
  {
    $num1 = floatval($num1);
    $num2 = floatval($num2);

    if ($num1 == 0) {
      return 0;
    }

    $perc = $num2 / $num1;
    return ($decimal == TRUE ? $perc : ($perc * 100));
  }

  /**
   * Calcula uma m�dia de acordo com uma f�rmula de FormulaMedia_Model_Media
   * da regra de avalia��o da s�rie/matr�cula do aluno.
   *
   * @param array $values
   * @return float
   */
  protected function _calculaMedia(array $values)
  {
    if (isset($values['Rc']) && $this->hasRecuperacao()) {
      $media = $this->getRegra()->formulaRecuperacao->execFormulaMedia($values);
    }
    else {
      $media = $this->getRegra()->formulaMedia->execFormulaMedia($values);
    }

    return $media;
  }

  /**
   * Insere ou atualiza as notas e/ou faltas que foram adicionadas ao service
   * e atualiza a matricula do aluno de acordo com a sua performance,
   * promovendo-o ou retendo-o caso o tipo de progress�o da regra de avalia��o
   * seja autom�tica (e que a situa��o do aluno n�o esteja em "andamento").
   *
   * @see Avaliacao_Service_Boletim#getSituacaoAluno()
   * @throws CoreExt_Service_Exception|Exception
   */
  public function save()
  {
    try {
      $this->saveNotas()
           ->saveFaltas()
           ->savePareceres()
           ->promover();
    }
    catch (CoreExt_Service_Exception $e) {
      throw $e;
    }
    catch (Exception $e) {
      throw $e;
    }
  }

  /**
   * Insere ou atualiza as notas no boletim do aluno.
   * @return Avaliacao_Service_Boletim Prov� interface flu�da
   */
  public function saveNotas()
  {
    if ($this->getRegra()->get('tipoNota') == RegraAvaliacao_Model_Nota_TipoValor::NENHUM) {
      return $this;
    }

    $notaAluno = $this->_getNotaAluno();
    $notas = $this->getNotas();

    foreach ($notas as $nota) {
      $nota->notaAluno = $notaAluno;
      $nota->id = $this->_getNotaIdEtapa($nota);
      $this->getNotaComponenteDataMapper()->save($nota);
    }

    // Atualiza as m�dias
    $this->_updateNotaComponenteMedia();

    return $this;
  }

  /**
   * Insere ou atualiza as faltas no boletim.
   * @return Avaliacao_Service_Boletim Prov� interface flu�da
   */
  public function saveFaltas()
  {
    $faltaAluno = $this->_getFaltaAluno();
    $faltas = $this->getFaltas();

    foreach ($faltas as $falta) {
      $falta->faltaAluno = $faltaAluno;
      $falta->id = $this->_getFaltaIdEtapa($falta);
      $this->getFaltaAbstractDataMapper()->save($falta);
    }

    return $this;
  }

  /**
   * Insere ou atualiza os pareceres no boletim.
   * @return Avaliacao_Service_Boletim Prov� interface flu�da
   */
  public function savePareceres()
  {
    $parecerAluno = $this->_getParecerDescritivoAluno();
    $pareceres    = $this->getPareceres();

    foreach ($pareceres as $parecer) {
      $parecer->parecerDescritivoAluno = $parecerAluno->id;
      $parecer->id = $this->_getParecerIdEtapa($parecer);
      $this->getParecerDescritivoAbstractDataMapper()->save($parecer);
    }

    return $this;
  }

  /**
   * Promove o aluno de etapa escolar caso esteja aprovado de acordo com o
   * necess�rio estabelecido por tipoProgressao de
   * RegraAvaliacao_Model_Regra.
   *
   * @param bool $ok Caso a progress�o n�o seja autom�tica, � necess�rio uma
   *   confirma��o externa para a promo��o do aluno.
   * @return bool
   */
  public function promover($ok = NULL)
  {
    $situacao = $this->getSituacaoAluno();

    if (TRUE == $situacao->andamento) {
      require_once 'CoreExt/Service/Exception.php';
      throw new CoreExt_Service_Exception('N�o � poss�vel promover uma matr�cula em andamento.');
    }
/*
    // Se a matr�cula j� foi alterada (aluno aprovado ou reprovado), lan�a exce��o.
    if (App_Model_MatriculaSituacao::EM_ANDAMENTO > $this->getOption('aprovado')) {
      require_once 'CoreExt/Service/Exception.php';
      throw new CoreExt_Service_Exception('A matr�cula j� foi promovida.');
    }
*/
    $tipoProgressao = $this->getRegra()->get('tipoProgressao');

    switch ($tipoProgressao) {
      case RegraAvaliacao_Model_TipoProgressao::CONTINUADA:
        $promover = TRUE;
        break;
      case RegraAvaliacao_Model_TipoProgressao::NAO_CONTINUADA_AUTO_MEDIA_PRESENCA:
        $promover = $situacao->aprovado && !$situacao->retidoFalta;
        break;
      case RegraAvaliacao_Model_TipoProgressao::NAO_CONTINUADA_AUTO_SOMENTE_MEDIA:
        $promover = $situacao->aprovado;
        break;
      case RegraAvaliacao_Model_TipoProgressao::NAO_CONTINUADA_MANUAL:
        if (is_null($ok)) {
          $enum = RegraAvaliacao_Model_TipoProgressao::getInstance();
          $message = sprintf('Para atualizar a matr�cula em uma regra %s � '
                   . 'necess�rio confirma��o externa no argumento "$ok".',
                   $enum->getValue($tipoProgressao));

          require_once 'CoreExt/Service/Exception.php';
          throw new CoreExt_Service_Exception($message);
        }

        $promover = $ok;
        break;
    }

    return $this->_updateMatricula($this->getOption('matricula'), $this->getOption('usuario'),
      $promover);
  }

  /**
   * Atualiza a m�dia dos componentes curriculares.
   */
  protected function _updateNotaComponenteMedia()
  {
    require_once 'Avaliacao/Model/NotaComponenteMedia.php';
    $this->_loadNotaComponenteCurricular(FALSE);

    foreach ($this->_notasComponentes as $id => $notasComponentes) {
      // Cria um array onde o �ndice � a etapa
      $etapasNotas = CoreExt_Entity::entityFilterAttr($notasComponentes, 'etapa', 'nota');
      $notas = array('Se' => 0, 'Et' => $this->getOption('etapas'));

      // Cria o array formatado para o c�lculo da f�rmula da m�dia
      foreach ($etapasNotas as $etapa => $nota) {
        if (is_numeric($etapa)) {
          $notas['E' . $etapa] = $nota;
          $notas['Se'] += $nota;
          continue;
        }
        $notas[$etapa] = $nota;
      }

      // Calcula a m�dia
      $media = $this->_calculaMedia($notas);

      // Cria uma nova inst�ncia de m�dia, j� com a nota arredondada e a etapa
      $notaComponenteCurricularMedia = new Avaliacao_Model_NotaComponenteMedia(array(
        'notaAluno' => $this->_getNotaAluno()->id,
        'componenteCurricular' => $id,
        'media' => $media,
        'mediaArredondada' => $this->arredondaNota($media),
        'etapa' => $etapa
      ));

      try {
        // Se existir, marca como "old" para possibilitar a atualiza��o
        $this->getNotaComponenteMediaDataMapper()->find(array(
          $notaComponenteCurricularMedia->get('notaAluno'),
          $notaComponenteCurricularMedia->get('componenteCurricular'),
        ));

        $notaComponenteCurricularMedia->markOld();
      }
      catch (Exception $e) {
        // Prossegue, sem problemas.
      }

      // Salva a m�dia
      $this->getNotaComponenteMediaDataMapper()->save($notaComponenteCurricularMedia);
    }
  }

  /**
   * Atualiza os dados da matr�cula do aluno.
   *
   * @param int $matricula
   * @param int $usuario
   * @param bool $promover
   * @return bool
   * @see App_Model_Matricula#atualizaMatricula($matricula, $usuario, $promover)
   */
  protected function _updateMatricula($matricula, $usuario, $promover)
  {
    return App_Model_Matricula::atualizaMatricula($matricula, $usuario, $promover);
  }
}
