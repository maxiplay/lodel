<?php
/**	
 * Script modifiant la base d'un site en 0.7 pour qu'elle puisse �tre utilis�e par un site en 0.8
 *
 * PHP versions 4 et 5
 *
 * LODEL - Logiciel d'Edition ELectronique.
 *
 * Home page: http://www.lodel.org
 * E-Mail: lodel@lodel.org
 *
 * All Rights Reserved
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 *
 * @author Sophie Malafosse
 * @author Pierre-Alain Mignot
 * @copyright 2001-2002, Ghislain Picard, Marin Dacos
 * @copyright 2003, Ghislain Picard, Marin Dacos, Luc Santeramo, Nicolas Nutten, Anne Gentil-Beccot
 * @copyright 2004, Ghislain Picard, Marin Dacos, Luc Santeramo, Anne Gentil-Beccot, Bruno C�nou
 * @copyright 2005, Ghislain Picard, Marin Dacos, Luc Santeramo, Gautier Poupeau, Jean Lamy, Bruno C�nou
 * @copyright 2006, Marin Dacos, Luc Santeramo, Bruno C�nou, Jean Lamy, Mika�l Cixous, Sophie Malafosse
 * @licence http://www.gnu.org/copyleft/gpl.html
 * @since Fichier ajout� depuis la version 0.8
 */
require_once 'func.php';

class exportfor08
{
	/**
	 * Toutes les requ�tes SQL effectu�es
	 * @var string
	 */
	public $requetes;

	/**
	 * �quivalences entre tables de la 0.7 et de la 0.8 : tableau utilis� pour transfert des donn�es vers les tables 0.8
	 * TABLE_08::
	 *	champ_1, champ_n,
	 * TABLE_07::
	 * 	champ_1, champ_n,
	 * @var array
	 */
	private $translations = array(
		'OBJECTS::
			id,class'=>
		'OBJETS::
			id,classe',

		'ENTITIES::
			id,idparent,idtype,identifier,usergroup,iduser,rank,status,upd'=>
		'ENTITES::
			id,idparent,idtype,identifiant,groupe,iduser,ordre,statut,maj',

		'RELATIONS::
			id1,id2,nature,degree'=>
		'RELATIONS::
			id1,id2,nature,degres',

		/* 
		TABLES PUBLICATIONS et DOCUMENTS en 0.7 : � recopier en 0.8 
		TABLE CLASSES en 0.8 : � remplir avec les classes (entit�s, index, index de personnes)
		*/

		'TABLEFIELDS::
			id,name,idgroup,title,style,type,cond,defaultvalue,processing,allowedtags,filtering,edition,comment,status,rank,upd'=>
		'CHAMPS::
			id,nom,idgroupe,titre,style,type,`condition`,defaut,traitement,balises,filtrage,edition,commentaire,statut,ordre,maj',

		'TABLEFIELDGROUPS::
			id,name,class,title,comment,status,rank,upd'=>
		'GROUPESDECHAMPS::
			id,nom,classe,titre,commentaire,statut,ordre,maj',

		'PERSONS::
			id,g_familyname,g_firstname,status,upd'=>
		'PERSONNES::
			id,nomfamille,prenom,statut,maj',

		'USERS::
			id,username,passwd,lastname,email,userrights,status,upd'=>
		'USERS::
			id,username,passwd,nom,courriel,privilege,statut,maj',
			// + champ expiration, en 0.7 seulement

		'USERGROUPS::
			id,name,status,upd'=>
		'GROUPES::
			id,nom,statut,maj',

		'USERS_USERGROUPS::
			idgroup,iduser'=>
		'USERS_GROUPES::
			idgroupe,iduser',
			
		'TYPES::
			id,type,title,class,tpl,tplcreation,tpledition,import,rank,status,upd'=>
		'TYPES::
			id,type,titre,classe,tpl,tplcreation,tpledition,import,ordre,statut,maj',

		/* 
		TABLES INTERNALSTYLES et CHARACTERSTYLES : en 0.8 seulement, cr��es par initdb()
		--> rien � faire
		*/

		'PERSONTYPES::
			id,type,title,style,tpl,tplindex,rank,status,upd'=>
		'TYPEPERSONNES::
			id,type,titre,style,tpl,tplindex,ordre,statut,maj',
			// champs pr�sents en 0.7 seulement : titredescription,styledescription

		'ENTRYTYPES::
			id,type,title,style,tpl,tplindex,rank,status,flat,newbyimportallowed,sort,upd'=>
		'TYPEENTREES::
			id,type,titre,style,tpl,tplindex,ordre,statut,lineaire,nvimportable,tri,maj',
			// champs pr�sents en 0.7 seulement : utiliseabrev

		'ENTRIES::
			id,idparent,g_name,idtype,rank,status,upd'=>
		'ENTREES::
			id,idparent,nom,idtype,ordre,statut,maj',
			// champs pr�sents en 0.7 seulement : abrev, lang

		'TASKS::
			id,name,step,user,context,status,upd'=>
		'TACHES::
			id,nom,etape,user,context,statut,maj',

		'TEXTS::
			id,name,contents,status,upd'=>
		'TEXTES::
			id,nom,texte,statut,maj',

		/*
		TABLES � SUPPRIMER : en 0.8, donn�es � transf�rer dans la table RELATIONS
		'entities_entries::
			identry,identity'=> 
		'ENTITES_ENTREES::
			identree,identite',

		'entities_persons::
			idperson,identity,idtype,rank,prefix,description,function,affiliation,email'=>
		'ENTITES_PERSONNES::
			idpersonne,identite,idtype,ordre,prefix,description,fonction,affiliation,courriel',
		*/

		'ENTITYTYPES_ENTITYTYPES::
			identitytype,identitytype2,cond'=>
		'TYPEENTITES_TYPEENTITES::
			idtypeentite,idtypeentite2,`condition`',

		/*
		TABLES � SUPPRIMER : en 0.8, donn�es � transf�rer dans la table entitytypes_entitytypes ???)
		'entitytypes_entrytypes::
			identitytype,identrytype,cond'=>
		'TYPEENTITES_TYPEENTREES::
			idtypeentite,idtypeentree,condition',

		'entitytypes_persontypes::
			identitytype,idpersontype,condition'=>
		'TYPEENTITES_TYPEPERSONNES::
			idtypeentite,idtypepersonne,cond',
		*/

		'OPTIONS::
			id,name,type,value,rank,status,upd'=>
		'OPTIONS::
			id,nom,type,valeur,ordre,statut,maj',

		/* 
		TABLES OPTIONGROUPS TRANSLATIONS SEARCH_ENGINE OAITOKENS OAILOGS : en 0.8 seulement, cr��es par initdb()
		--> rien � faire
		*/	

		/*
		TABLES globales Lodel : � faire ici ??
		'translations::
			id,lang,title,textgroups,translators,modificationdate,creationdate,rank,status,upd'=>
		'TRANSLATIONS::
			'id,lang,titre,textgroups,translators,modificationdate,creationdate,ordre,statut,maj'*/
		);

	/**
	 * Constructeur
	 */
	function __construct() {
		$this->old_tables = $this->get_tables();
	}

	/**
	 *  R�cup�re la liste des tables dans la base du site
	 *
	 * @return array
	 */

	private function get_tables() {
		$result=mysql_list_tables($GLOBALS[currentdb]);
		$tables=array();
		while (list($table) = mysql_fetch_row($result)) {
			$tables[] = $table;
		}
		if (!empty($tables)) {
			return $tables;
		} else {
			return 'Pas de tables � traiter';
		}
	}

	/**
	 * Renomme les tables de la 0.7 : ajoute le suffixe __old au nom de la table
	 * Cr�e ensuite les tables de la 0.8, d'apr�s le fichier init-site.sql
	 *
	 * @return array
	 * @todo Ne renommer que les tables de la 0.7
	 */
	public function init_db() {
		if (!in_array($GLOBALS['tp'] . 'objets__old', $this->old_tables)) {
	
			// sauvegarde des tables en 0.7 : renomm�es en $table . __old
			$query = '';
			foreach ($this->old_tables as $table07) {
				if (substr($table07, -5) != '__old') {
					$query .= "RENAME TABLE _PREFIXTABLE_$table07 TO _PREFIXTABLE_$table07" . '__old;';
				}
			}
			if ($err = $this->__mysql_query_cmds($query)) {
				return $err;
			}
			
			// import de la structure des tables en 0.8
			$query = '';
			$sqlfile = SITEROOT . 'init-site.sql';
			if (is_readable($sqlfile)) {
				$query = join('', file($sqlfile));
			} else {
				return 'Impossible de lire le fichier init-site.sql pour la 0.8';
			}
			if ($err = $this->__mysql_query_cmds($query)) {
				return $err;
			}
	
			// cr�ations tables qui d�pendent du ME en 0.8 (et qui sont en dur en 0.7)
			// ENTIT�S : publications, documents
			$query = "CREATE TABLE IF NOT EXISTS _PREFIXTABLE_publications AS SELECT * FROM _PREFIXTABLE_publications__old;
				ALTER TABLE _PREFIXTABLE_publications CHANGE identite identity INT UNSIGNED DEFAULT '0' NOT NULL UNIQUE;
				CREATE TABLE IF NOT EXISTS _PREFIXTABLE_documents AS SELECT * FROM _PREFIXTABLE_documents__old;
				ALTER TABLE _PREFIXTABLE_documents CHANGE identite identity INT UNSIGNED DEFAULT '0' NOT NULL UNIQUE;
				ALTER TABLE _PREFIXTABLE_documents ADD alterfichier tinytext;";
	
			// INDEX : indexes
			$query .= "CREATE TABLE IF NOT EXISTS _PREFIXTABLE_indexes (
					identry int(10) unsigned default NULL,
					nom text,
					definition text,
					UNIQUE KEY identry (identry),
					KEY index_identry (identry)
					);";
	
			// INDEX DE PERSONNES : auteurs et entities_auteurs
			$query .= "CREATE TABLE IF NOT EXISTS _PREFIXTABLE_auteurs(
					idperson int(10) unsigned default NULL,
					nomfamille tinytext,
					prenom tinytext,
					UNIQUE KEY idperson (idperson),
					KEY index_idperson (idperson)
					);";
	
			$query .= "CREATE TABLE IF NOT EXISTS _PREFIXTABLE_entities_auteurs(
					idrelation int(10) unsigned default NULL,
					prefix tinytext,
					affiliation tinytext,
					fonction tinytext,
					description text,
					courriel text,
					role text,
					site text,
					UNIQUE KEY idrelation (idrelation),
					KEY index_idrelation (idrelation)
					);";
	
			$query .= "CREATE TABLE IF NOT EXISTS _PREFIXTABLE_fichiers (
					identity int(10) unsigned default NULL,
					titre text,
					document tinytext,
					description text,
					legende text,
					credits tinytext,
					vignette tinytext,
					UNIQUE KEY identity (identity),
					KEY index_identity (identity)
					);";
	
			$query .= "CREATE TABLE IF NOT EXISTS _PREFIXTABLE_liens (
				identity int(10) unsigned default NULL,
				titre text,
				url text,
				urlfil text,
				texte text,
				capturedecran tinytext,
				nombremaxitems int(11) default NULL,
				UNIQUE KEY identity (identity),
				KEY index_identity (identity)
					);";
	
			if ($err = $this->__mysql_query_cmds($query)) {
				return $err;
			} else {
	// 			echo '<p>initdb ok</p>';
				return "Ok";
			}
		} else {
			return 'Aucune table d�tect�e.';
		}
	}


	/**
	 *  Copie les donn�es de la 0.7 dans les tables de la 0.8
	 *
	 * @return true si la copie est ok
	 */
	public function cp_07_to_08() {
		
		if(!$result = mysql_query('select * from ' . $GLOBALS['tp'] . 'objects')) {
			return mysql_error();
		}
		$num_rows = mysql_num_rows($result);
		if ($num_rows == 0) {

			foreach ($this->translations as $new => $old) {
				list($newtable, $newfields) = explode("::", strtolower($new));
				list($oldtable, $oldfields) = explode("::", strtolower($old));
				$oldfields = trim($oldfields);
				$newfields = trim($newfields);
				$oldtable .= '__old';

				if ($err = $this->__mysql_query_cmds("INSERT INTO _PREFIXTABLE_$newtable ($newfields) SELECT $oldfields FROM _PREFIXTABLE_$oldtable;")) {
					return $err;
				}
			}
		}
		if(!$req = mysql_query("SELECT id, g_familyname, g_firstname FROM ".$GLOBALS['tp']."persons;")) {
			return mysql_error();
		}
		while($res = mysql_fetch_array($req)) {
			if ($err = $this->__mysql_query_cmds("UPDATE _PREFIXTABLE_persons SET sortkey = \"".strtolower($res['g_familyname']." ".$res['g_firstname'])."\" WHERE id = '".$res['id']."';")) {
				return $err;
			}			
		}
		return "Ok";
	}

	/**
	 * Cr�ation des classes : en dur en 0.7 (publications, documents, index et index de personnes)
	 * En mou en 0.8, donc � ins�rer dans les tables objects et classes
	 * 
	 * @return true si insertions dans les tables OK
	 */

	public function create_classes() {
		// ENTIT�S : publications et documents
		$id = $this->__insert_object('classes');
		if(!is_int($id))
			return $id;
		$query = "INSERT IGNORE INTO `_PREFIXTABLE_classes` (`id` , `icon` , `class` , `title` , `altertitle` , `classtype` , `comment` , `rank` , `status` , `upd` )
		VALUES ($id , 'lodel/icons/collection.gif', 'publications', 'Publications', '', 'entities', '', '1', '1', NOW( ));
		UPDATE _PREFIXTABLE_objects SET class='entities' WHERE class='publications';
		";
	
		$id = $this->__insert_object('classes');
		$query .= "INSERT IGNORE INTO `_PREFIXTABLE_classes` ( `id` , `icon` , `class` , `title` , `altertitle` , `classtype` , `comment` , `rank` , `status` , `upd` )
		VALUES ($id , 'lodel/icons/texte.gif', 'documents', 'Documents', '', 'entities', '', '2', '1', NOW( ));
		UPDATE _PREFIXTABLE_objects SET class='entities' WHERE class='documents';
		";

		// INDEX
		$id = $this->__insert_object('classes');
		$query .= "INSERT IGNORE INTO `_PREFIXTABLE_classes` ( `id` , `icon` , `class` , `title` , `altertitle` , `classtype` , `comment` , `rank` , `status` , `upd` )
		VALUES ($id , 'lodel/icons/index.gif', 'indexes', 'Index', '', 'entries', '', '3', '1', NOW( ));
		UPDATE _PREFIXTABLE_objects SET class='entries' WHERE class='entrees';
		UPDATE _PREFIXTABLE_objects SET class='entrytypes' WHERE class='typeentrees';
		";

		// INDEX DE PERSONNES
		$id = $this->__insert_object('classes');
		$query .= "INSERT IGNORE INTO `_PREFIXTABLE_classes` ( `id` , `icon` , `class` , `title` , `altertitle` , `classtype` , `comment` , `rank` , `status` , `upd` )
		VALUES ($id , 'lodel/icons/personne.gif', 'auteurs', 'Auteurs', '', 'persons', '', '4', '1', NOW( ));
		UPDATE _PREFIXTABLE_objects SET class='persons' WHERE class='personnes';
		UPDATE _PREFIXTABLE_objects SET class='persontypes' WHERE class='typepersonnes';
		";

		if ($err = $this->__mysql_query_cmds($query)) {
			return $err;
		} else {
			$ret = $this->__update_entities();
			if($ret !== true)
				return $ret;
		}
		return "Ok";
	}

	/**
	 * Insertion d'une ligne dans la table objects
	 * 
	 * @return l'identifiant ins�r� (champ auto-incr�ment� id)
	 */

	private function __insert_object($class) {
		$this->requetes .= 'INSERT INTO ' . $GLOBALS['tp'] . "objects (class) VALUES ('$class');";
		if(!$result = mysql_query('INSERT INTO ' . $GLOBALS['tp'] . "objects (class) VALUES ('$class')")) {
			return mysql_error();
		}
		return mysql_insert_id();
	}

	/**
	 * Mise � jour de la table entities avec les donn�es issues des tables documents et publications
	 *
	 * @param string $title nom du champ qui fait office de titre dans le ME de la 0.7, pour les classes documents et publications : vaut 'titre' dans le ME revues.org 0.7
	 * @todo g�n�rer aussi les champs suivants : identifier, creationdate, modificationdate, creationmethod, creationinfo
	 * @todo demander � l'utilisateur le nom du champ, au cas o� il serait diff�rent de 'titre'
	 *
	 * @return true si OK
	 */

	private function __update_entities($title = 'titre') {
		
		foreach (array("publications","documents") as $classe) {
	  		if(!$result = mysql_query('SELECT identity,' . $title . ' FROM ' . $GLOBALS['tp'] . $classe)) {
				return mysql_error();
			}
	  		while (list($id,$titre) = mysql_fetch_row($result)) {
	    			$titre = strip_tags($titre);
	    			if (strlen($titre)>255) {
	      				$titre=substr($titre,0,256);
	      				$titre=preg_replace("/\S+$/","",$titre);
	    			}
	    			$titre = addslashes($titre);
				$query = 'UPDATE ' . $GLOBALS['tp'] . "entities set g_title='$titre' WHERE id=$id;";
				$this->requetes .= $query;
				if(!mysql_query($query))
					return mysql_error();
	  		}
		}
		return true;
	}

	/**
	 * Mise � jour des champs pour les classes
	 * Pour les entit�s, reprise des champs des tables publications et documents
	 * Pour les index et index de personnes, ajout dans table tablefields
	 *
	 * @param string $dctitle nom du champ qui fait office de titre dans le ME de la 0.7, pour les classes documents et publications : vaut 'titre' dans le ME revues.org 0.7
	 * @todo demander � l'utilisateur le nom du champ, au cas o� il serait diff�rent de 'titre'
	 * @return true si insertions dans les tables OK
	 */

	public function update_fields($dctitle = 'titre') {
		// ENTIT�S : mise � jour des colonnes 'class' et 'g_name' seulement
		if(!$result = mysql_query("SELECT id,class FROM $GLOBALS[tp]tablefieldgroups WHERE status>0")) {
			return mysql_error();
		}
		$query = '';
		while ($row = mysql_fetch_assoc($result)) {
			$query .= "UPDATE _PREFIXTABLE_tablefields SET g_name = 'dc.title', class='" . $row['class'] . "' WHERE idgroup = " . $row['id'] . ';';
		}
		if(!$result = mysql_query("SELECT $GLOBALS[tp]entites__old.id, $GLOBALS[tp]entites__old.maj, $GLOBALS[tp]documents__old.fichiersource FROM $GLOBALS[tp]entites__old JOIN $GLOBALS[tp]documents__old ON ($GLOBALS[tp]entites__old.id = $GLOBALS[tp]documents__old.identite)")) {
			return mysql_error();
		}
		while ($row = mysql_fetch_assoc($result)) {
			$this->requetes .= "UPDATE $GLOBALS[tp]entities SET creationmethod = 'servoo', creationdate = '".$row['maj']."', modificationdate = '".$row['maj']."', creationinfo = \"".$row['fichiersource']."\" WHERE id = " . $row['id'] . ';';
			if(!mysql_query("UPDATE $GLOBALS[tp]entities SET creationmethod = 'servoo', creationdate = '".$row['maj']."', modificationdate = '".$row['maj']."', creationinfo = \"".$row['fichiersource']."\" WHERE id = " . $row['id'] . ';')) {
				return mysql_error();
			}
		}

		// INDEX : ajout des champs dans tablefields
		$query .= "INSERT INTO _PREFIXTABLE_tablefields (id, name, idgroup, class, title, altertitle, style, type, g_name, cond, defaultvalue, processing, allowedtags, gui_user_complexity, filtering, edition, editionparams, weight, comment, status, rank, upd) VALUES
			(NULL, 'nom', '0', 'indexes', 'D�nomination de l\'entr�e d\'index', '', '', 'text', 'index key', '*', 'Tous droits r�serv�s', '', '', '16', '', 'editable', '', '4', '', '32', '', NOW( )),

			(NULL, 'definition', '0', 'indexes', 'D�finition', '', '', 'text', '', '*', '', '', '', '16', '', 'fckeditor', 'Basic', '1', '', '32', '', NOW( ));";

		// INDEX DE PERSONNES : ajout des champs dans tablefields
		$query .= "INSERT INTO _PREFIXTABLE_tablefields (id, name, idgroup, class, title, altertitle, style, type, g_name, cond, defaultvalue, processing, allowedtags, gui_user_complexity, filtering, edition, editionparams, weight, comment, status, rank, upd) VALUES
			(NULL, 'nomfamille', '0', 'auteurs', 'Nom de famille', '', '', 'tinytext', 'familyname', '*', '', '', '', '32', '', 'editable', '', '4', '', '32', '', NOW( )),

			(NULL, 'prenom', '0', 'auteurs', 'Pr�nom', '', '', 'tinytext', 'firstname', '*', '', '', '', '32', '', 'editable', '', '4', '', '32', '', NOW( )),

			(NULL, 'prefix', '0', 'entities_auteurs', 'Pr�fixe', '', 'prefixe, .prefixe', 'tinytext', '', '*', '', '', '', '64', '', 'editable', '', '0', '', '1', '', NOW( )),

			(NULL, 'affiliation', '0', 'entities_auteurs', 'Affiliation', '', 'affiliation, .affiliation', 'tinytext', '', '*', '', '', '', '32', '', 'editable', '', '4', '', '1', '', NOW( )),
			
			(NULL, 'fonction', '0', 'entities_auteurs', 'Fonction', '', 'fonction, .fonction', 'tinytext', '', '*', '', '', '', '32', '', 'editable', '', '0', '', '1', '', NOW( )),

			(NULL, 'description', '0', 'entities_auteurs', 'Description de l\'auteur', '', 'descriptionauteur', 'text', '', '*', '', '', '', '16', '', 'fckeditor', '5', '4', '', '1', '', NOW( )),

			(NULL, 'courriel', '0', 'entities_auteurs', 'Courriel', '', 'courriel, .courriel', 'email', '', '*', '', '', '', '32', '', 'editable', '', '4', '', '1', '', NOW( )),

			(NULL, 'role', '0', 'entities_auteurs', 'Role dans l\'�laboration du document', '', 'role,.role', 'text', '', '*', '', '', '', '64', '', 'editable', '', '0', '', '1', '', NOW( ));";

		if ($err = $this->__mysql_query_cmds($query)) {
				return $err;
		} else {
			return "Ok";
		}
	}

	/**
	 * Mise � jour des types
	 * 
	 * @return true si insertions dans les tables OK
	 */

	public function update_types() {
		// ENTITES
		$query = "UPDATE _PREFIXTABLE_types SET display='';";

		// INDEX
		$query .= "UPDATE _PREFIXTABLE_entrytypes SET class = 'indexes', sort = 'sortkey';";
		
		// INDEX DE PERSONNES
		$query .= "UPDATE _PREFIXTABLE_persontypes SET class = 'auteurs';
		UPDATE _PREFIXTABLE_persons JOIN _PREFIXTABLE_entites_personnes__old ON id = idpersonne SET _PREFIXTABLE_persons.idtype = _PREFIXTABLE_entites_personnes__old.idtype;
		";
		
		if ($err = $this->__mysql_query_cmds($query)) {
				return $err;
		} else {
// echo '<p>update_types ok</p>';
			return "Ok";
		}
	}

	/**
	 * Remplissage des tables des index et index de personnes � partir des tables 0.7 (entrees et auteurs)
	 * Mise � jour des relations : en 0.8, toutes les relations sont stock�es dans la table relations,
	 * en 0.7 dans les tables entites_entrees et entites_personnes
	 * 
	 * @return true si insertions dans les tables OK
	 */

	public function insert_index_data() {
		// INDEX : tables indexes et relations
		if(!$result = mysql_query('SELECT MAX(idrelation) FROM ' . $GLOBALS['tp'] . 'relations')) {
			return mysql_error();
		}
		$max_id = mysql_result($result, 0);
		$query = "REPLACE INTO _PREFIXTABLE_indexes (identry, nom) SELECT id, g_name from _PREFIXTABLE_entries;
		INSERT INTO _PREFIXTABLE_relations (id2, id1) SELECT DISTINCT identree, identite from _PREFIXTABLE_entites_entrees__old;
		UPDATE _PREFIXTABLE_relations SET nature='E', degree=1 WHERE degree IS NULL AND idrelation > $max_id;
		";
		mysql_free_result($result);
		// INDEX DE PERSONNES : tables auteurs, entities_auteurs et relations
		if(!$result = mysql_query('SELECT MAX(idrelation) FROM ' . $GLOBALS['tp'] . 'relations')) {
			return mysql_error();
		}
		$max_id = mysql_result($result, 0);
		$query .= "REPLACE INTO _PREFIXTABLE_auteurs (idperson, nomfamille, prenom) SELECT id, g_familyname, g_firstname from _PREFIXTABLE_persons;
		INSERT INTO _PREFIXTABLE_relations (id2, id1) SELECT DISTINCT idpersonne, identite from _PREFIXTABLE_entites_personnes__old;
		UPDATE _PREFIXTABLE_relations SET nature='G', degree=1 WHERE degree IS NULL AND idrelation > $max_id;
		REPLACE INTO _PREFIXTABLE_entities_auteurs (idrelation, prefix, affiliation, fonction, description, courriel) SELECT DISTINCT idrelation, prefix, affiliation, fonction, description, courriel from relations, entites_personnes__old where nature='G' and idpersonne=id2 and identite=id1;
		";
		mysql_free_result($result);

		if ($err = $this->__mysql_query_cmds($query)) {
				return $err;
		} else {
			$nature = array('1'=>'G', '2'=>'E');
			$j = 1;
			while($j < 3) {
				if(!$result = mysql_query("SELECT * FROM " . $GLOBALS['tp'] . "relations WHERE nature='".$nature[$j]."' AND id1 != 0 ORDER BY id1, id2")) {
					return mysql_error();
				}
				while($res = mysql_fetch_array($result)) {
					$i = 1;
					if(!$re = mysql_query("SELECT id2 FROM " . $GLOBALS['tp'] . "relations WHERE id1 = '".$res['id1']."' AND nature = '".$nature[$j]."' ORDER BY id2")) {
						return mysql_error();
					}
					while($resu = mysql_fetch_array($re)) {
						$this->requetes .= "UPDATE " . $GLOBALS['tp'] . "relations SET degree = ".$i." WHERE id1 = '".$res['id1']."' AND id2 = '".$resu['id2']."' AND nature = '".$nature[$j]."';";
						if(!mysql_query("UPDATE " . $GLOBALS['tp'] . "relations SET degree = ".$i." WHERE id1 = '".$res['id1']."' AND id2 = '".$resu['id2']."' AND nature = '".$nature[$j]."'")) {
							return mysql_error();
						}
						$i++;
					}
				}
				$j++;
			}
// 			echo '<p>insert_index_data ok</p>';
			return "Ok";
		}
	}

	/**
	 * Mise � jour du ME pour conformit� avec ME revues.org de la 0.8
	 * 
	 * @return true si insertions dans les tables OK
	 */

	public function update_ME() {
		// classe documents devient textes
		$this->requetes .= "RENAME TABLE ".$GLOBALS[tp]."documents TO ".$GLOBALS[tp]."textes;";
		if(!mysql_query("RENAME TABLE ".$GLOBALS[tp]."documents TO ".$GLOBALS[tp]."textes")) {
			return mysql_error();
		}
		$query = "UPDATE _PREFIXTABLE_classes SET class = 'textes',title = 'Textes' WHERE class = 'documents';
		UPDATE _PREFIXTABLE_objects SET class = 'textes' WHERE class='documents';
		UPDATE _PREFIXTABLE_types SET class = 'textes' WHERE class='documents';
		UPDATE _PREFIXTABLE_tablefields SET class = 'textes' WHERE class='documents';
		UPDATE _PREFIXTABLE_tablefieldgroups SET class = 'textes' WHERE class='documents';
		";

		// Nom des TEMPLATES dans l'onglet �dition
		$query .= "UPDATE _PREFIXTABLE_types SET tpledition = 'edition',tplcreation = 'entities';";

		// CLASSES suppl�mentaires
		$query .= "INSERT IGNORE INTO `_PREFIXTABLE_classes` (`id` , `icon` , `class` , `title` , `altertitle` , `classtype` , `comment` , `rank` , `status` , `upd` ) VALUES
		(NULL, 'lodel/icons/doc_annexe.gif', 'fichiers', 'Fichiers', '', 'entities', '', '5', '32', NOW()),
		(NULL, 'lodel/icons/lien.gif', 'liens', 'Sites', '', 'entities', '', '6', '32', NOW()),
		(NULL, 'lodel/icons/texte_simple.gif', 'textessimples', 'Textes simples', '', 'entities', '', '3', '32', NOW()),
		(NULL, 'lodel/icons/individu.gif', 'individus', 'Personnes', '', 'entities', '', '4', '1', NOW()),
		(NULL, 'lodel/icons/index_avance.gif', 'indexavances', 'Index avanc�s', '', 'entries', '', '10', '1', NOW());
		
		CREATE TABLE _PREFIXTABLE_textessimples (
  			identity int(10) unsigned default NULL,
  			titre tinytext,
  			texte text,
  			url text,
  			`date` datetime default NULL,
  			UNIQUE KEY identity (identity),
  			KEY index_identity (identity)
		);

		CREATE TABLE _PREFIXTABLE_individus (
  			identity int(10) unsigned default NULL,
  			nom tinytext,
  			prenom tinytext,
  			email text,
  			siteweb text,
  			description text,
  			accroche text,
  			adresse text,
  			telephone tinytext,
  			photographie tinytext,
  			UNIQUE KEY identity (identity),
  			KEY index_identity (identity)
		);

		CREATE TABLE _PREFIXTABLE_indexavances (
			identry int(10) unsigned default NULL,
  			nom tinytext,
  			description text,
  			url text,
  			icone tinytext,
  			UNIQUE KEY identry (identry),
  			KEY index_identry (identry)
		);

		";

		// TYPES
		if(!$result = mysql_query('SELECT MAX(id) FROM ' . $GLOBALS['tp'] . 'types')) {
			return mysql_error();
		}
		$max_id = mysql_result($result, 0);
		$q = utf8_encode("INSERT INTO " . $GLOBALS['tp'] . "types (id, icon, type, title, altertitle, class, tpl, tplcreation, tpledition, import, display, creationstatus, search, public, gui_user_complexity, oaireferenced, rank, status, upd) VALUES 

		(NULL, 'lodel/icons/rubrique_plat.gif', 'rubriqueaplat', 'Sous-partie', '', 'publications', '', 'entities', 'edition', '0', 'unfolded', '-1', '1', '0', '16', '0', '6', '32', NOW()),
		(NULL, '', 'image', 'Image', '', 'fichiers', 'image', 'entities', '', '0', '', '-1', '1', '0', '64', '1', '1', '1', NOW()),
		(NULL, '', 'noticedesite', 'Notice de site', '', 'liens', 'lien', 'entities', '', '0', '', '-1', '1', '0', '64', '0', '16', '1', NOW()),
		(NULL, 'lodel/icons/commentaire.gif', 'commentaire', 'Commentaire du document', '', 'textessimples', '', 'entities', '', '0', 'advanced', '-1', '1', '1', '16', '0', '2', '1', NOW()),
		(NULL, '', 'videoannexe', 'Vid�o plac�e en annexe', '', 'fichiers', '', 'entities', 'edition', '0', 'advanced', '-1', '1', '0', '64', '0', '4', '1', NOW()),
		(NULL, '', 'annuairedepersonnes', 'Biographies des membres', '', 'publications', 'sommaire', 'entities', 'edition', '0', '', '-1', '1', '0', '16', '0', '8', '32', NOW()),
		(NULL, '', 'annuairemedias', 'M�diath�que', '', 'publications', 'sommaire', 'entities', 'edition', '0', '', '-1', '1', '0', '16', '0', '9', '32', NOW()),
		(NULL, '', 'image_annexe', 'Image plac�e en annexe', '', 'fichiers', '', 'entities', '', '0', 'advanced', '-1', '1', '0', '64', '0', '2', '1', NOW()),
		(NULL, '', 'lienannexe', 'Lien plac� en annexe', '', 'liens', 'lien', 'entities', '', '0', 'advanced', '-1', '1', '0', '64', '0', '24', '1', NOW()),
		(NULL, '', 'individu', 'Notice biographique de membre', '', 'individus', 'individu', 'entities', '', '0', '', '-1', '1', '0', '16', '0', '25', '1', NOW()),
		(NULL, '', 'billet', 'Billet', '', 'textessimples', 'article', 'entities', '', '0', '', '-1', '1', '0', '16', '0', '1', '1', NOW()),
		(NULL, '', 'annuairedesites', 'Annuaire de sites', '', 'publications', 'sommaire', 'entities', 'edition', '0', '', '-1', '1', '0', '16', '0', '7', '32', NOW()),
		(NULL, 'lodel/icons/rss.gif', 'fluxdesyndication', 'Flux de syndication', '', 'liens', 'lien', 'entities', '', '0', '', '-1', '1', '0', '64', '0', '30', '1', NOW()),
		(NULL, '', 'video', 'Vid�o', '', 'fichiers', '', 'entities', '', '0', '', '-1', '1', '0', '64', '0', '3', '1', NOW()),
		(NULL, '', 'son', 'Document sonore', '', 'fichiers', '', 'entities', '', '0', '', '-1', '1', '0', '32', '0', '5', '1', NOW()),
		(NULL, '', 'fichierannexe', 'Fichier plac� en annexe', '', 'fichiers', 'image', 'entities', '', '0', 'advanced', '-1', '1', '0', '32', '0', '7', '1', NOW()),
		(NULL, '', 'sonannexe', 'Document sonore plac� en annexe', '', 'fichiers', '', 'entities', '', '0', 'advanced', '-1', '1', '0', '32', '0', '6', '1', NOW()),
		(NULL, '', 'imageaccroche', 'Image d\'accroche', '', 'fichiers', 'image', 'entities', '', '0', 'advanced', '-1', '1', '0', '16', '0', '31', '32', NOW()),
		(NULL, 'lodel/icons/rubrique.gif', 'rubriqueannuaire', 'Rubrique (d\'annuaire de site)', '', 'publications', 'sommaire', 'entities', 'edition', '0', '', '-1', '1', '0', '16', '0', '32', '32', NOW()),
		(NULL, '', 'rubriquemediatheque', 'Rubrique (de m�diath�que)', '', 'publications', 'sommaire', 'entities', 'edition', '0', '', '-1', '1', '0', '16', '0', '33', '32', NOW()),
		(NULL, 'lodel/icons/rubrique.gif', 'rubriqueequipe', 'Rubrique (d\'�quipe)', '', 'publications', 'sommaire', 'entities', 'edition', '0', 'unfolded', '-1', '1', '0', '16', '0', '34', '32', NOW()),
		(NULL, 'lodel/icons/rubrique.gif', 'rubriqueactualites', 'Rubrique (d\'actualit�s)', '', 'publications', 'sommaire', 'entities', 'edition', '0', '', '-1', '1', '0', '16', '0', '35', '32', NOW());");
		$this->requetes .= $q;
		if(!mysql_query($q)) {
			return mysql_error();
		}

		$prerequete = "INSERT INTO _PREFIXTABLE_entitytypes_entitytypes (identitytype, identitytype2, cond) VALUES ('8', '0', '*'),
				('11', '11', '*'),
				('11', '9', '*'),
				('1', '10', '*'),
				('2', '327', '*'),
				('2', '10', '*'),
				('3', '328', '*'),
				('20', '8', '*'),
				('3', '11', '*'),
				('3', '327', '*'),
				('3', '81', '*'),
				('3', '10', '*'),
				('21', '10', '*'),
				('4', '9', '*'),
				('4', '8', '*'),
				('4', '21', '*'),
				('5', '328', '*'),
				('6', '8', '*'),
				('6', '21', '*'),
				('6', '19', '*'),
				('6', '20', '*'),
				('7', '9', '*'),
				('7', '8', '*'),
				('7', '21', '*'),
				('7', '19', '*'),
				('26', '6', '*'),
				('14', '5', '*'),
				('13', '11', '*'),
				('13', '327', '*'),
				('13', '10', '*'),
				('20', '0', '*'),
				('14', '6', '*'),
				('14', '1', '*'),
				('14', '4', '*'),
				('14', '7', '*'),
				('26', '1', '*'),
				('1', '9', '*'),
				('9', '8', '*'),
				('1', '8', '*'),
				('1', '21', '*'),
				('2', '9', '*'),
				('14', '2', '*'),
				('14', '3', '*'),
				('14', '13', '*'),
				('12', '11', '*'),
				('12', '10', '*'),
				('19', '10', '*'),
				('19', '8', '*'),
				('14', '12', '*'),
				('26', '4', '*'),
				('25', '18', '*'),
				('25', '6', '*'),
				('25', '5', '*'),
				('25', '1', '*'),
				('25', '4', '*'),
				('25', '7', '*'),
				('25', '2', '*'),
				('25', '3', '*'),
				('2', '8', '*'),
				('5', '11', '*'),
				('19', '0', '*'),
				('12', '9', '*'),
				('13', '9', '*'),
				('15', '5', '*'),
				('15', '6', '*'),
				('15', '1', '*'),
				('15', '4', '*'),
				('15', '7', '*'),
				('15', '2', '*'),
				('15', '3', '*'),
				('16', '1', '*'),
				('16', '4', '*'),
				('16', '7', '*'),
				('16', '2', '*'),
				('16', '3', '*'),
				('16', '10', '*'),
				('16', '9', '*'),
				('21', '8', '*'),
				('18', '327', '*'),
				('21', '0', '*'),
				('22', '11', '*'),
				('22', '327', '*'),
				('22', '10', '*'),
				('22', '9', '*'),
				('22', '8', '*'),
				('17', '10', '*'),
				('23', '11', '*'),
				('23', '10', '*'),
				('24', '11', '*'),
				('24', '10', '*'),
				('24', '9', '*'),
				('26', '7', '*'),
				('26', '2', '*'),
				('26', '3', '*'),
				('26', '10', '*'),
				('26', '9', '*'),
				('27', '18', '*'),
				('27', '5', '*'),
				('27', '6', '*'),
				('27', '1', '*'),
				('27', '4', '*'),
				('27', '7', '*'),
				('27', '2', '*'),
				('27', '3', '*'),
				('17', '20', '*'),
				('22', '328', '*'),
				('13', '328', '*'),
				('24', '328', '*'),
				('10', '10', '*'),
				('10', '8', '*'),
				('18', '10', '*'),
				('23', '9', '*'),
				('23', '21', '*'),
				('24', '21', '*'),
				('12', '8', '*'),
				('12', '21', '*'),
				('18', '9', '*'),
				('18', '8', '*'),
				('13', '8', '*'),
				('13', '21', '*'),
				('22', '21', '*'),
				('22', '19', '*'),
				('18', '21', '*'),
				('18', '19', '*'),
				('18', '20', '*'),
				('4', '19', '*'),
				('5', '327', '*'),
				('16', '6', '*'),
				('16', '5', '*'),
				('26', '5', '*'),
				('26', '18', '*'),
				('326', '6', '*'),
				('326', '1', '*'),
				('326', '4', '*'),
				('326', '7', '*'),
				('326', '2', '*'),
				('326', '3', '*'),
				('326', '328', '*'),
				('326', '329', '*'),
				('326', '11', '*'),
				('326', '327', '*'),
				('326', '10', '*'),
				('326', '9', '*'),
				('326', '8', '*'),
				('326', '21', '*'),
				('326', '19', '*'),
				('326', '20', '*'),
				('326', '13', '*'),
				('326', '22', '*'),
				('22', '20', '*'),
				('327', '19', '*'),
				('327', '327', '*'),
				('328', '328', '*'),
				('328', '21', '*'),
				('329', '20', '*'),
				('329', '329', '*'),
				('13', '19', '*'),
				('22', '0', '*'),
				('1', '19', '*'),
				('2', '21', '*'),
				('2', '19', '*'),
				('3', '9', '*'),
				('3', '8', '*'),
				('3', '21', '*'),
				('3', '19', '*'),
				('1', '327', '*'),
				('1', '11', '*'),
				('1', '328', '*'),
				('2', '11', '*'),
				('2', '328', '*'),
				('4', '10', '*'),
				('4', '327', '*'),
				('4', '11', '*'),
				('4', '328', '*'),
				('5', '10', '*'),
				('5', '9', '*'),
				('5', '8', '*'),
				('5', '21', '*'),
				('5', '19', '*'),
				('7', '10', '*'),
				('7', '327', '*'),
				('7', '11', '*'),
				('7', '328', '*'),
				('12', '328', '*'),
				('23', '328', '*'),
				('18', '11', '*'),
				('18', '328', '*'),
				('17', '329', '*'),
				('326', '5', '*'),
				('326', '18', '*'),
				('326', '14', '*'),
				('81', '8', '*');";

		$correspondances = array('editorial'=>'1', 
					'article'=>'2',
					'actualite'=>'3',
					'compte rendu'=>'4',
					'note de lecture'=>'5',
					'informations'=>'6',
					'chronique'=>'7',
					'collection'=>'8',
					'numero'=>'9',
					'rubrique'=>'10',
					'rubriqueaplat'=>'11',
					'image'=>'12',
					'noticedesite'=>'13',
					'commentaire'=>'14',
					'image_annexe'=>'15',
					'lienannexe'=>'16',
					'individu'=>'17',
					'billet'=>'18',
					'annuairedesites'=>'19',
					'annuairedequipe'=>'20',
					'annuairemedias'=>'21',
					'fluxdesyndication'=>'22',
					'video'=>'23',
					'son'=>'24',
					'videoannexe'=>'25',
					'fichierannexe'=>'26',
					'sonannexe'=>'27',
					'rubriqueactualites'=>'81',
					'imageaccroche'=>'326',
					'rubriqueannuaire'=>'327',
					'rubriquemediatheque'=>'328',
					'rubriqueequipe'=>'329');

		mysql_free_result($result);
		if(!$result = mysql_query("SELECT id, type FROM " . $GLOBALS['tp'] . "types WHERE id > ".$max_id.";")) {
			return mysql_error();
		}
		while($res = mysql_fetch_array($result)) {
			$prerequete = str_replace("'".$correspondances[$res['type']]."'", "'".$res['id']."'", $prerequete);
		}

		$query .= $prerequete;
		$query .= "UPDATE _PREFIXTABLE_types SET class = 'liens', tpl = 'lien', tplcreation = 'entities', tpledition = '', display = 'advanced' WHERE type = 'documentannexe-liendocument' OR type = 'documentannexe-lienpublication' OR type = 'documentannexe-lienexterne';";
		$query .= "UPDATE _PREFIXTABLE_types SET class = 'fichiers', tpl = 'image', tplcreation = 'entities', display = 'advanced', tpledition = '' WHERE type = 'documentannexe-lienfichier';";
		$query .= "UPDATE _PREFIXTABLE_types SET display = 'advanced' WHERE type = 'documentannexe-lienfichier';";
		$query .= "UPDATE _PREFIXTABLE_types SET display = 'unfolded' WHERE type = 'regroupement';";
		$query .= "UPDATE _PREFIXTABLE_types set tpledition = '' WHERE class = 'textes';";
		$query .= "UPDATE _PREFIXTABLE_types set tpl = '' WHERE class = 'textessimples';";
		$query .= "DELETE FROM _PREFIXTABLE_types where type = 'documentannexe-lienfacsimile';";

		// entrytypes
		$query .= "UPDATE _PREFIXTABLE_entrytypes SET tpl = 'entree', tplindex = 'entrees', edition = 'pool';";
		for($i=0;$i<4;$i++) {
			$id[] = $this->__insert_object('entrytypes');
		}
		$query .= "INSERT INTO _PREFIXTABLE_entrytypes (id, icon, type, class, title, altertitle, style, g_type, tpl, tplindex, gui_user_complexity, rank, status, flat, newbyimportallowed, edition, sort, upd) VALUES
		(".$id[0].", '', 'motscleses', 'indexes', 'Indice de palabras clave', '', 'palabrasclaves, .palabrasclaves, motscleses', '', 'entree', 'entrees', '64', '9', '1', '0', '1', 'pool', 'sortkey', NOW()),
		(".$id[1].", '', 'licence', 'indexavances', 'Licence portant sur le document', '', 'licence, droitsauteur', 'dc.rights', 'entree', 'entrees', '16', '7', '1', '1', '1', 'select', 'rank', NOW()),
		(".$id[2].", '', 'motsclede', 'indexes', 'Schlagwortindex', '', 'schlagworter, .schlagworter, motsclesde', '', 'entree', 'entrees', '32', '8', '1', '0', '0', 'pool', 'sortkey', NOW()),
		(".$id[3].", '', 'motsclesen', 'indexes', 'Index by keyword', '', 'keywords, motclesen', '', 'entree', 'entrees', '64', '2', '1', '1', '1', 'pool', 'sortkey', NOW());
		";
		$query .= "UPDATE _PREFIXTABLE_entrytypes SET style = 'geographie, gographie,.geographie' WHERE type = 'geographie';";
		$query .= "UPDATE _PREFIXTABLE_entrytypes SET style = 'themes,thmes,.themes', gui_user_complexity = 16, rank = 6 WHERE type = 'theme';";
		$query .= "UPDATE _PREFIXTABLE_entrytypes SET type = 'chrono', style = 'periode, .periode, priode', rank = 5 WHERE type = 'periode';";
		$query .= "UPDATE _PREFIXTABLE_entrytypes SET title = 'Index de mots-cl�s', style = 'motscles, .motcles,motscls,motsclesfr', g_type = 'dc.subject', gui_user_complexity = 32 WHERE type = 'motcle';";

		// OPTIONS
		$query .= "INSERT INTO _PREFIXTABLE_optiongroups (id, idparent, name, title, altertitle, comment, logic, exportpolicy, rank, status, upd) VALUES
			('4', '0', 'from07', 'Suite import de donn�es de Lodel 0.7', '', '', '', '1', '1', '32', NOW()),
			('1', '0', 'servoo', 'Servoo', '', '', 'servooconf', '1', '1', '32', NOW()),
			('2', '0', 'metadonneessite', 'M�tadonn�es du site', '', '', '', '1', '2', '1', NOW()),
			('3', '0', 'oai', 'OAI', '', '', '', '1', '5', '1', NOW());
		UPDATE _PREFIXTABLE_options SET idgroup = 4, title = 'Signaler par mail' WHERE name = 'signaler_mail';
		UPDATE _PREFIXTABLE_options SET idgroup = 3, userrights = 40 WHERE name LIKE 'oai_%';
		UPDATE _PREFIXTABLE_options SET idgroup = 2, title = 'ISSN �lectronique', name = 'ISSN_electronique', userrights = 30, rank = 7, status = 32 WHERE name = 'issn_electronique';
		UPDATE _PREFIXTABLE_options SET type = 'tinytext' WHERE type = 's';
		UPDATE _PREFIXTABLE_options SET type = 'passwd' WHERE type = 'pass';
		UPDATE _PREFIXTABLE_options SET type = 'email' WHERE type = 'mail';
		UPDATE _PREFIXTABLE_options SET title = 'oai_allow' WHERE name = 'oai_allow';
		UPDATE _PREFIXTABLE_options SET title = 'oai_deny' WHERE name = 'oai_deny';
		UPDATE _PREFIXTABLE_options SET title = 'Email de l\'administrateur du d�p�t' WHERE name = 'oai_email';
		INSERT INTO _PREFIXTABLE_options (id, idgroup, name, title, type, defaultvalue, comment, userrights, rank, status, upd, edition, editionparams) VALUES 
			(NULL, '1', 'url', 'url', 'tinytext', '', '', '40', '1', '32', NOW(), 'editable', ''),
			(NULL, '1', 'username', 'username', 'username', '', '', '40', '2', '32', NOW(), 'editable', ''),
			(NULL, '1', 'passwd', 'password', 'passwd', '', '', '40', '3', '32', NOW(), '', ''),
			(NULL, '2', 'titresite', 'Titre du site', 'tinytext', 'Titresite', '', '40', '1', '1', NOW(), '', ''),
			(NULL, '2', 'titresiteabrege', 'Titre abr�g� du site', 'tinytext', 'Titre abr�g� du site', '', '40', '3', '1', NOW(), '', ''),
			(NULL, '2', 'descriptionsite', 'Description du site', 'text', '', '', '40', '4', '1', NOW(), 'textarea', ''),
			(NULL, '2', 'urldusite', 'URL officielle du site', 'url', '', '', '40', '5', '1', NOW(), 'editable', ''),
			(NULL, '2', 'issn', 'ISSN', 'tinytext', '', '', '30', '6', '1', NOW(), 'editable', ''),
			(NULL, '2', 'editeur', 'Nom de l\'�diteur du site', 'tinytext', '', '', '30', '8', '1', NOW(), '', ''),
			(NULL, '2', 'adresseediteur', 'Adresse postale de l\'�diteur', 'text', '', '', '30', '9', '1', NOW(), '', ''),
			(NULL, '2', 'producteursite', 'Nom du producteur du site', 'tinytext', '', '', '30', '10', '1', NOW(), '', ''),
			(NULL, '2', 'diffuseursite', 'Nom du diffuseur du site', 'tinytext', '', '', '30', '11', '1', NOW(), '', ''),
			(NULL, '2', 'droitsauteur', 'Droits d\'auteur par d�faut', 'tinytext', '', '', '30', '12', '1', NOW(), '', ''),
			(NULL, '2', 'directeurpublication', 'Nom du directeur de la publication', 'tinytext', '', '', '30', '13', '1', NOW(), '', ''),
			(NULL, '2', 'redacteurenchef', 'Nom du R�dacteur en chef', 'tinytext', '', '', '30', '14', '1', NOW(), '', ''),
			(NULL, '2', 'courrielwebmaster', 'Courriel du webmaster', 'email', '', '', '30', '15', '1', NOW(), '', ''),
			(NULL, '2', 'courrielabuse', 'Courriel abuse', 'tinytext', '', '', '40', '16', '1', NOW(), 'editable', ''),
			(NULL, '2', 'motsclesdusite', 'Mots cl�s d�crivant le site (entre virgules)', 'text', '', '', '30', '17', '1', NOW(), '', ''),
			(NULL, '2', 'langueprincipale', 'Langue principale du site', 'lang', 'fr', '', '40', '18', '1', NOW(), 'editable', ''),
			(NULL, '2', 'soustitresite', 'Sous titre du site', 'tinytext', '', '', '40', '2', '1', NOW(), 'editable', '');
		";

		// persontypes
		$q = "SELECT id, type FROM ".$GLOBALS['tp']."persontypes;";
		$this->requetes .= $q;
		if(!$req = mysql_query($q)) {
			return mysql_error();
		}
		unset($id);
		for($i=0;$i<5;$i++) {
			$id[] = $this->__insert_object('persontypes');
		}
		$query .= "REPLACE INTO _PREFIXTABLE_persontypes (id, icon, type, title, altertitle, class, style, g_type, tpl, tplindex, gui_user_complexity, rank, status, upd) VALUES 
		(".$id[0].", '', 'traducteur', 'Traducteur', '', 'auteurs', 'traducteur', 'dc.contributor', 'personne', 'personnes', '64', '2', '1', NOW()),
		(".$id[1].", '', 'auteuroeuvre', 'Auteur d\'une oeuvre comment�e', '', 'auteurs', 'auteuroeuvre', '', 'personne', 'personnes', '64', '4', '32', NOW()),
		(".$id[2].", '', 'editeurscientifique', 'Editeur scientifique', '', 'auteurs', 'editeurscientifique', '', 'personne', 'personnes', '64', '5', '1', NOW()),
		(".$id[3].", 'lodel/icons/auteur.gif', 'auteur', 'Auteur', '', 'auteurs', 'auteur', 'dc.creator', 'personne', 'personnes', '32', '1', '1', NOW()),
		(".$id[4].", '', 'directeur de publication', 'Directeur de la publication', '', 'auteurs', 'directeur', '', 'personne', 'personnes', '32', '3', '32', NOW());
		";
		while($res = mysql_fetch_array($req)) {
			$query .= "UPDATE _PREFIXTABLE_persontypes SET id = ".$res['id']." WHERE type = \"".$res['type']."\";";
		}
 		$query .= "UPDATE _PREFIXTABLE_persontypes SET type = 'directeurdelapublication' WHERE title = 'Directeur de la publication';";

		// styles internes
		$query .= "INSERT INTO _PREFIXTABLE_internalstyles (id, style, surrounding, conversion, greedy, rank, status, upd) VALUES 
			(NULL, 'citation', '*-', '<blockquote>', '0', '1', '1', NOW()),
			(NULL, 'quotations', '*-', '<blockquote>', '0', '2', '1', NOW()),
			(NULL, 'citationbis', '*-', '<blockquote class=\"citationbis\">', '0', '3', '1', NOW()),
			(NULL, 'citationter', '*-', '<blockquote class=\"citationter\">', '0', '4', '1', NOW()),
			(NULL, 'titreillustration', '*-', '', '0', '5', '1', NOW()),
			(NULL, 'legendeillustration', '*-', '', '0', '6', '1', NOW()),
			(NULL, 'titredoc', '*-', '', '0', '7', '1', NOW()),
			(NULL, 'legendedoc', '*-', '', '0', '8', '1', NOW()),
			(NULL, 'puces', '*-', '<ul><li>', '0', '9', '1', NOW()),
			(NULL, 'code', '*-', '', '0', '10', '1', NOW()),
			(NULL, 'question', '*-', '', '0', '11', '1', NOW()),
			(NULL, 'reponse', '*-', '', '0', '12', '1', NOW()),
			(NULL, 'separateur', '*-', '<hr style=\"style\">', '0', '19', '1', NOW()),
			(NULL, 'section1', '-*', '<h1>', '0', '13', '1', NOW()),
			(NULL, 'section3', '*-', '<h3>', '0', '15', '1', NOW()),
			(NULL, 'section4', '*-', '<h4>', '0', '16', '1', NOW()),
			(NULL, 'section5', '*-', '<h5>', '0', '17', '1', NOW()),
			(NULL, 'section6', '*-', '<h6>', '0', '18', '1', NOW()),
			(NULL, 'paragraphesansretrait', '*-', '', '0', '20', '1', NOW()),
			(NULL, 'epigraphe', '*-', '', '0', '21', '1', NOW()),
			(NULL, 'section2', '-*', '<h2>', '0', '14', '1', NOW()),
			(NULL, 'pigraphe', '-*', '', '0', '22', '1', NOW()),
			(NULL, 'sparateur', '-*', '', '0', '23', '1', NOW()),
			(NULL, 'quotation', '-*', '<blockquote>', '0', '24', '1', NOW()),
			(NULL, 'terme', '-*', '', '0', '25', '1', NOW()),
			(NULL, 'definitiondeterme', '-*', '', '0', '26', '1', NOW()),
			(NULL, 'bibliographieannee', '-*', '', '0', '27', '1', NOW()),
			(NULL, 'bibliographieauteur', 'bibliographie', '', '0', '28', '1', NOW()),
			(NULL, 'bibliographiereference', 'bibliographie', '', '0', '29', '1', NOW()),
			(NULL, 'creditillustration,crditillustration,creditsillustration,crditsillustration', '-*', '', '0', '30', '1', NOW()),
			(NULL, 'remerciements', '-*', '', '0', '31', '1', NOW());";

		// textes
		$this->requetes .= "RENAME TABLE ".$GLOBALS[tp]."textes TO ".$GLOBALS[tp]."textes__oldME;";
		if(!mysql_query("RENAME TABLE ".$GLOBALS[tp]."textes TO ".$GLOBALS[tp]."textes__oldME")) {
			return mysql_error();
		}

		$q = "CREATE TABLE ".$GLOBALS[tp]."textes (
				identity int(10) unsigned default NULL,
				titre text,
				surtitre text,
				soustitre text,
				texte longtext,
				notesbaspage longtext,
				annexe text,
				bibliographie text,
				datepubli date default NULL,
				datepublipapier date default NULL,
				noticebiblio text,
				pagination tinytext,
				langue char(5) default NULL,
				prioritaire tinyint(4) default NULL,
				addendum text,
				ndlr text,
				commentaireinterne text,
				dedicace text,
				ocr tinyint(4) default NULL,
				documentcliquable tinyint(4) default NULL,
				`resume` text,
				altertitre text,
				titreoeuvre text,
				noticebibliooeuvre text,
				datepublicationoeuvre tinytext,
				ndla text,
				icone tinytext,
				alterfichier tinytext,
				numerodocument double default NULL,
				notefin longtext,
				UNIQUE KEY identity (identity),
				KEY index_identity (identity)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		$this->requetes .= $q;
		if(!mysql_query($q)) {
			return mysql_error();
		}
		$q = "INSERT INTO ".$GLOBALS[tp]."textes (identity, titre, surtitre, soustitre, texte, notesbaspage, annexe, bibliographie, datepubli, datepublipapier, noticebiblio, pagination, langue, prioritaire, ndlr, commentaireinterne, resume, icone, alterfichier, notefin) SELECT identity, titre, surtitre, soustitre, texte, notebaspage, annexe, bibliographie, datepubli, datepublipapier, noticebiblio, pagination, langue, prioritaire, ndlr, commentaireinterne, resume, icone, alterfichier, notefin FROM ".$GLOBALS[tp]."textes__oldME;";
		$this->requetes .= $q;
		if(!mysql_query($q)) {
			return mysql_error();
		}

		// publications
		$this->requetes .= "RENAME TABLE ".$GLOBALS[tp]."publications TO ".$GLOBALS[tp]."publications__oldME;";
		if(!mysql_query("RENAME TABLE ".$GLOBALS[tp]."publications TO ".$GLOBALS[tp]."publications__oldME")) {
			return mysql_error();
		}
		
		$q = "CREATE TABLE ".$GLOBALS[tp]."publications (
				identity int(10) unsigned default NULL,
				titre text,
				surtitre text,
				soustitre text,
				commentaireinterne text,
				prioritaire tinyint(4) default NULL,
				datepubli date default NULL,
				datepublipapier date default NULL,
				noticebiblio text,
				introduction text,
				ndlr text,
				historique text,
				periode tinytext,
				isbn tinytext,
				paraitre tinyint(4) default NULL,
				integralite tinyint(4) default NULL,
				numero tinytext,
				icone tinytext,
				langue varchar(5) default NULL,
				altertitre text,
				urlpublicationediteur text,
				descriptionouvrage text,
				erratum text,
				UNIQUE KEY identity (identity),
				KEY index_identity (identity)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		$this->requetes .= $q;
		if(!mysql_query($q)) {
			mysql_error();
		}
		$q = "INSERT INTO ".$GLOBALS[tp]."publications (identity, titre, surtitre, soustitre, commentaireinterne, prioritaire, datepubli, datepublipapier, noticebiblio, introduction, ndlr, historique, icone, erratum) SELECT identity, titre, surtitre, soustitre, commentaireinterne, prioritaire, datepubli, datepublipapier, noticebiblio, introduction, ndlr, historique, icone, erratum FROM ".$GLOBALS[tp]."publications__oldME;";
		$this->requetes .= $q;
		if(!mysql_query($q)) {
			return mysql_error();
		}

		// tablefieldgroups
		$query .= "DELETE FROM _PREFIXTABLE_tablefieldgroups;
		INSERT INTO _PREFIXTABLE_tablefieldgroups (id, name, class, title, altertitle, comment, status, rank, upd) VALUES 
			('1', 'grtitre', 'textes', 'Titres', '', '', '1', '1', NOW()),
			('2', 'grtexte', 'textes', 'Texte', '', '', '1', '3', NOW()),
			('3', 'grmeta', 'textes', 'M�tadonn�es', '', '', '1', '4', NOW()),
			('4', 'graddenda', 'textes', 'Addenda', '', '', '1', '5', NOW()),
			('5', 'grtitre', 'liens', 'Titre', '', '', '1', '5', NOW()),
			('6', 'grsite', 'liens', 'D�finition du site', '', '', '1', '6', NOW()),
			('7', 'grtitre', 'fichiers', 'Titre', '', '', '1', '7', NOW()),
			('8', 'grmultimedia', 'fichiers', 'D�finition', '', '', '1', '8', NOW()),
			('9', 'grresumes', 'textes', 'R�sum�s', '', '', '1', '2', NOW()),
			('10', 'grtitre', 'publications', 'Groupe de titre', '', '', '32', '1', NOW()),
			('11', 'grgestion', 'publications', 'Gestion des publications', '', '', '1', '4', NOW()),
			('12', 'grmetadonnees', 'publications', 'Groupe des m�tadonn�es', '', '', '32', '3', NOW()),
			('13', 'graddenda', 'publications', 'Groupe des addenda', '', '', '32', '2', NOW()),
			('14', 'grpersonnes', 'textes', 'Auteurs', '', '', '1', '7', NOW()),
			('15', 'grindex', 'textes', 'Index', '', '', '1', '6', NOW()),
			('16', 'grgestion', 'textes', 'Gestion du document', '', '', '1', '9', NOW()),
			('17', 'grrecension', 'textes', 'Oeuvre comment�e (si ce document est un compte-rendu d\'oeuvre ou d\'ouvrage...)', '', '', '1', '8', NOW()),
			('18', 'grtitre', 'textessimples', 'Titre', '', '', '1', '10', NOW()),
			('19', 'grtexte', 'textessimples', 'Texte', '', '', '1', '11', NOW()),
			('24', 'grdroits', 'fichiers', 'Droits', '', '', '32', '16', NOW()),
			('25', 'grauteurs', 'liens', 'Auteurs', '', '', '32', '17', NOW()),
			('26', 'grauteurs', 'textessimples', 'Auteurs', '', '', '32', '18', NOW()),
			('28', 'grtitre', 'individus', 'Titre', '', '', '1', '20', NOW()),
			('30', 'grdescription', 'individus', 'Description', '', '', '1', '21', NOW());";

		// tablefields
		$this->requetes .= "DELETE FROM ".$GLOBALS['tp']."tablefields;";
		if(!mysql_query("DELETE FROM ".$GLOBALS['tp']."tablefields;")) {
			return mysql_error();
		}
		$q = utf8_encode("INSERT INTO ".$GLOBALS['tp']."tablefields (id, name, idgroup, class, title, altertitle, style, type, g_name, cond, defaultvalue, processing, allowedtags, gui_user_complexity, filtering, edition, editionparams, weight, comment, status, rank, upd) VALUES 
		(NULL, 'titre', '1', 'textes', 'Titre du document', '', 'title, titre, titleuser, heading', 'text', 'dc.title', '+', 'Document sans titre', '', 'xhtml:fontstyle;xhtml:phrase;xhtml:special;Lien;Appel de Note', '16', '', 'editable', '', '8', '', '32', '3', NOW()),
		(NULL, 'surtitre', '1', 'textes', 'Surtitre du document', '', 'surtitre', 'text', '', '*', '', '', 'xhtml:fontstyle;xhtml:phrase;xhtml:special;Lien;Appel de Note', '32', '', 'importable', '', '8', '', '32', '2', NOW()),
		(NULL, 'soustitre', '1', 'textes', 'Sous-titre du document', '', 'subtitle, soustitre', 'text', '', '*', '', '', 'xhtml:fontstyle;xhtml:phrase;xhtml:special;Lien;Appel de Note', '32', '', 'editable', '', '8', '', '32', '5', NOW()),
		(NULL, 'texte', '2', 'textes', 'Texte du document', '', 'texte, standard, normal, textbody', 'longtext', '', '*', '', '', 'xhtml:fontstyle;xhtml:phrase;xhtml:special;xhtml:block;Lien;Appel de Note', '16', '', 'display', '', '4', '', '32', '1', NOW()),
		(NULL, 'notesbaspage', '2', 'textes', 'Notes de bas de page', '', 'notebaspage, footnote, footnotetext', 'text', '', '*', '', '', 'xhtml:fontstyle;xhtml:phrase;xhtml:special;xhtml:block;Lien', '32', '', 'importable', '', '4', '', '32', '2', NOW()),
		(NULL, 'annexe', '2', 'textes', 'Annexes du document', '', 'annexe', 'text', '', '*', '', '', 'xhtml:fontstyle;xhtml:phrase;xhtml:special;xhtml:block;Lien;Appel de Note', '32', '', 'importable', '', '4', '', '32', '4', NOW()),
		(NULL, 'bibliographie', '2', 'textes', 'Bibliographie du document', '', 'bibliographie', 'text', '', '*', '', '', 'xhtml:fontstyle;xhtml:phrase;xhtml:special;xhtml:block;Lien;Appel de Note', '32', '', 'importable', '', '4', '', '32', '5', NOW()),
		(NULL, 'datepubli', '3', 'textes', 'Date de la publication �lectronique', '', 'datepubli', 'date', 'dc.date', '*', 'today', '', '', '16', '', 'editable', '', '0', '', '32', '1', NOW()),
		(NULL, 'datepublipapier', '3', 'textes', 'Date de la publication sur papier', '', 'datepublipapier', 'date', '', '*', '', '', '', '32', '', 'editable', '', '0', '', '32', '2', NOW()),
		(NULL, 'noticebiblio', '3', 'textes', 'Notice bibliographique du document', '', 'noticebiblio', 'text', '', '*', '', '', 'xhtml:fontstyle;xhtml:phrase;xhtml:special', '64', '', 'importable', '', '0', '', '32', '3', NOW()),
		(NULL, 'pagination', '3', 'textes', 'Pagination du document sur le papier', '', 'pagination', 'tinytext', '', '*', '', '', '', '64', '', 'editable', '', '0', '', '32', '4', NOW()),
		(NULL, 'editeurscientifique', '14', 'textes', '�diteur scientifique', '', '', 'persons', '', '', '', '', '', '64', '', 'editable', '', '0', '', '1', '109', NOW()),
		(NULL, 'langue', '3', 'textes', 'Langue du document', '', 'langue', 'lang', 'dc.language', '*', 'fr', '', '', '32', '', 'editable', '', '0', '', '1', '6', NOW()),
		(NULL, 'prioritaire', '16', 'textes', 'Document prioritaire', '', '', 'boolean', '', '*', '', '', '', '64', '', 'editable', '', '0', '', '32', '7', NOW()),
		(NULL, 'addendum', '4', 'textes', 'Addendum', '', 'erratum, addendum', 'text', '', '*', '', '', 'xhtml:fontstyle;xhtml:phrase;xhtml:special;xhtml:block;Lien;Appel de Note', '64', '', 'importable', '', '2', '', '32', '3', NOW()),
		(NULL, 'ndlr', '4', 'textes', 'Note de la r�daction', '', 'ndlr', 'text', '', '*', '', '', 'xhtml:fontstyle;xhtml:phrase;xhtml:special;xhtml:block;Lien;Appel de Note', '64', '', 'importable', '', '2', '', '32', '1', NOW()),
		(NULL, 'commentaireinterne', '16', 'textes', 'Commentaire interne sur le document', '', 'commentaire', 'text', '', '*', '', '', 'xhtml:fontstyle;xhtml:phrase;xhtml:special;xhtml:block;Lien', '64', '', 'importable', '', '0', '', '32', '4', NOW()),
		(NULL, 'dedicace', '4', 'textes', 'D�dicace', '', 'dedicace', 'text', '', '*', '', '', 'xhtml:fontstyle;xhtml:phrase;xhtml:special;xhtml:block;Lien;Appel de Note', '64', '', 'importable', '', '2', '', '32', '4', NOW()),
		(NULL, 'ocr', '16', 'textes', 'Document issu d\'une num�risation dite OCR', '', '', 'boolean', '', '*', '', '', '', '64', '', 'importable', '', '0', '', '32', '9', NOW()),
		(NULL, 'documentcliquable', '16', 'textes', 'Document cliquable dans les sommaires', '', '', 'boolean', '', '*', 'true', '', '', '64', '', 'editable', '', '0', '', '32', '10', NOW()),
		(NULL, 'nom', '0', 'indexes', 'D�nomination de l\'entr�e d\'index', '', '', 'text', 'index key', '*', 'Tous droits r�serv�s', '', '', '16', '', 'editable', '', '4', '', '32', '25', NOW()),
		(NULL, 'motcle', '15', 'textes', 'Index de mots-cl�s', '', '', 'entries', '', '', '', '', '', '64', '', 'editable', '', '0', '', '32', '2', NOW()),
		(NULL, 'definition', '0', 'indexes', 'D�finition', '', '', 'text', '', '*', '', '', 'xhtml:fontstyle;xhtml:phrase;xhtml:special;xhtml:block;Lien', '16', '', 'fckeditor', 'Basic', '1', '', '32', '27', NOW()),
		(NULL, 'nomfamille', '0', 'auteurs', 'Nom de famille', '', '', 'tinytext', 'familyname', '*', '', '', '', '32', '', 'editable', '', '4', '', '32', '28', NOW()),
		(NULL, 'prenom', '0', 'auteurs', 'Pr�nom', '', '', 'tinytext', 'firstname', '*', '', '', '', '32', '', 'editable', '', '4', '', '32', '29', NOW()),
		(NULL, 'prefix', '0', 'entities_auteurs', 'Pr�fixe', '', 'prefixe, .prefixe', 'tinytext', '', '*', '', '', '', '64', '', 'editable', '', '0', '', '1', '2', NOW()),
		(NULL, 'affiliation', '0', 'entities_auteurs', 'Affiliation', '', 'affiliation, .affiliation', 'tinytext', '', '*', '', '', '', '32', '', 'editable', '', '4', '', '1', '3', NOW()),
		(NULL, 'fonction', '0', 'entities_auteurs', 'Fonction', '', 'fonction, .fonction', 'tinytext', '', '*', '', '', '', '32', '', 'editable', '', '0', '', '1', '4', NOW()),
		(NULL, 'description', '0', 'entities_auteurs', 'Description de l\'auteur', '', 'descriptionauteur', 'text', '', '*', '', '', 'xhtml:fontstyle;xhtml:phrase;xhtml:special;Lien', '16', '', 'fckeditor', '5', '4', '', '1', '1', NOW()),
		(NULL, 'courriel', '0', 'entities_auteurs', 'Courriel', '', 'courriel, .courriel', 'email', '', '*', '', '', '', '32', '', 'editable', '', '4', '', '1', '5', NOW()),
		(NULL, 'auteur', '14', 'textes', 'Auteur du document', '', '', 'persons', '', '', '', '', '', '64', '', 'editable', '', '0', '', '32', '11', NOW()),
		(NULL, 'traducteur', '14', 'textes', 'Traducteur du document', '', '', 'persons', '', '', '', '', '', '64', '', 'editable', '', '0', '', '32', '12', NOW()),
		(NULL, 'alias', '16', 'textes', 'Alias', '', '', 'entities', '', '*', '', '', '', '64', '', 'editable', '', '0', '', '1', '119', NOW()),
		(NULL, 'date', '19', 'textessimples', 'Date de publication en ligne', '', '', 'datetime', '', '*', 'now', '', '', '16', '', 'editable', '', '0', '', '1', '100', NOW()),
		(NULL, 'url', '19', 'textessimples', 'Lien', '', '', 'url', '', '*', '', '', '', '16', '', 'editable', '', '2', '', '1', '99', NOW()),
		(NULL, 'licence', '24', 'fichiers', 'Licence', '', '', 'entries', '', '', '', '', '', '64', '', 'editable', '', '0', '', '1', '118', NOW()),
		(NULL, 'titre', '5', 'liens', 'Titre du site', '', '', 'text', 'dc.title', '*', 'Site sans titre', '', 'xhtml:fontstyle;xhtml:phrase;xhtml:special;Appel de Note', '16', '', 'editable', '', '8', '', '32', '43', NOW()),
		(NULL, 'url', '6', 'liens', 'URL du site', '', '', 'url', '', '*', '', '', '', '16', '', 'editable', '', '0', '', '32', '1', NOW()),
		(NULL, 'urlfil', '6', 'liens', 'URL du fil de syndication du site', '', '', 'url', '', '*', '', '', '', '16', '', 'editable', '', '0', '', '32', '4', NOW()),
		(NULL, 'texte', '6', 'liens', 'Description du site', '', '', 'text', '', '*', '', '', 'xhtml:fontstyle;xhtml:phrase;xhtml:special;xhtml:block;Lien;Appel de Note', '16', '', 'fckeditor', 'Simple', '2', '', '32', '2', NOW()),
		(NULL, 'titre', '7', 'fichiers', 'Titre', '', '', 'text', 'dc.title', '*', '', '', 'xhtml:fontstyle;xhtml:phrase;xhtml:special;Appel de Note', '16', '', 'editable', '', '4', '', '32', '47', NOW()),
		(NULL, 'document', '8', 'fichiers', 'Document', '', '', 'file', '', '*', '', '', '', '16', '', 'editable', '', '0', '', '32', '1', NOW()),
		(NULL, 'altertitre', '1', 'textes', 'Titre alternatif du document (dans une autre langue)', '', 'titretraduitfr:fr,titretraduiten:en,titretraduites:es,titretraduitpt:pt,titretraduitit:it,titretraduitde:de,titretraduitru:ru,titleen:en,titoloit:it,titelde:de,tituloes:es', 'mltext', '', '*', '', '', 'xhtml:fontstyle;xhtml:phrase;xhtml:special;Lien;Appel de Note', '16', '', 'editable', '', '8', '', '32', '4', NOW()),
		(NULL, 'resume', '9', 'textes', 'R�sum�', '', 'rsum,resume:fr,resumefr:fr,abstract:en,resumeen:en,extracto:es,resumen:es, resumees:es,resumo:pt,resumept:pt,riassunto:it,resumeit:it,zusammenfassung:de,resumede:de,resumeru:ru', 'mltext', 'dc.description', '*', '', '', 'xhtml:fontstyle;xhtml:phrase;xhtml:special;xhtml:block;Lien;Appel de Note', '16', '', 'display', '5', '8', '', '32', '50', NOW()),
		(NULL, 'titre', '10', 'publications', 'Titre de la publication', '', 'title, titre, titleuser, heading', 'text', 'dc.title', '*', '', '', 'xhtml:fontstyle;xhtml:phrase;xhtml:special;Appel de Note', '16', '', 'editable', '', '8', '', '32', '2', NOW()),
		(NULL, 'surtitre', '10', 'publications', 'Surtitre de la publication', '', 'surtitre', 'text', '', '*', '', '', 'xhtml:fontstyle;xhtml:phrase;xhtml:special;Appel de Note', '16', '', 'importable', '', '8', '', '32', '1', NOW()),
		(NULL, 'soustitre', '10', 'publications', 'Sous-titre de la publication', '', 'soustitre', 'text', '', '*', '', '', 'xhtml:fontstyle;xhtml:phrase;xhtml:special;Appel de Note', '16', '', 'editable', '', '8', '', '32', '3', NOW()),
		(NULL, 'commentaireinterne', '11', 'publications', 'Commentaire interne sur la publication', '', '', 'text', '', '*', '', '', 'xhtml:fontstyle;xhtml:phrase;xhtml:special;xhtml:block;Lien', '64', '', 'editable', '4', '0', '', '32', '54', NOW()),
		(NULL, 'prioritaire', '11', 'publications', 'Cette publication est-elle prioritaire ?', '', '', 'boolean', '', '*', '', '', '', '16', '', 'editable', '', '0', '', '32', '55', NOW()),
		(NULL, 'datepubli', '12', 'publications', 'Date de publication �lectronique', '', '', 'date', 'dc.date', '*', 'today', '', '', '16', '', 'editable', '', '0', '', '32', '2', NOW()),
		(NULL, 'datepublipapier', '12', 'publications', 'Date de publication papier', '', '', 'date', '', '*', '', '', '', '16', '', 'editable', '', '0', '', '32', '3', NOW()),
		(NULL, 'noticebiblio', '12', 'publications', 'Notice bibliographique d�crivant la publication', '', '', 'text', '', '*', '', '', 'xhtml:fontstyle;xhtml:phrase;xhtml:special', '64', '', 'importable', '', '0', '', '32', '4', NOW()),
		(NULL, 'introduction', '13', 'publications', 'Introduction de la publication', '<r2r:ml lang=\"fr\">Introduction de la publication</r2r:ml>', 'texte, standard, normal', 'text', '', '*', '', '', 'xhtml:fontstyle;xhtml:phrase;xhtml:special;xhtml:block;Lien;Appel de Note', '16', '', 'fckeditor', 'Simple,550,400', '8', '', '32', '60', NOW()),
		(NULL, 'geographie', '15', 'textes', 'Index g�ographique', '', '', 'entries', '', '', '', '', '', '64', '', 'editable', '', '0', '', '1', '110', NOW()),
		(NULL, 'chrono', '15', 'textes', 'Index chronologique', '', '', 'entries', '', '', '', '', '', '64', '', 'editable', '', '0', '', '1', '111', NOW()),
		(NULL, 'ndlr', '13', 'publications', 'Note de la r�daction au sujet de la publication', '', '', 'text', '', '*', '', '', 'xhtml:fontstyle;xhtml:phrase;xhtml:special;xhtml:block;Lien;Appel de Note', '64', '', 'fckeditor', '', '2', '', '32', '62', NOW()),
		(NULL, 'historique', '13', 'publications', 'Historique de la publication', '', '', 'text', '', '*', '', '', 'xhtml:fontstyle;xhtml:phrase;xhtml:special;xhtml:block;Lien;Appel de Note', '64', '', 'importable', '', '0', '', '32', '63', NOW()),
		(NULL, 'periode', '12', 'publications', 'P�riode de publication', '', '', 'tinytext', '', '*', '', '', '', '16', '', 'importable', '', '0', '', '1', '5', NOW()),
		(NULL, 'isbn', '12', 'publications', 'ISBN', '', '', 'tinytext', '', '*', '', '', '', '16', '', 'editable', '', '0', '', '1', '7', NOW()),
		(NULL, 'paraitre', '11', 'publications', 'Cette publication est-elle à paraitre ?', '', '', 'boolean', '', '*', '', '', '', '32', '', 'editable', '', '0', '', '32', '66', NOW()),
		(NULL, 'integralite', '11', 'publications', 'Cette publication en ligne est-elle int�grale ?', '', '', 'boolean', '', '*', '', '', '', '32', '', 'editable', '', '0', '', '32', '67', NOW()),
		(NULL, 'numero', '12', 'publications', 'Num�ro de la publication', '', '', 'tinytext', '', '*', '', '', '', '16', '', 'editable', '', '0', '', '32', '6', NOW()),
		(NULL, 'motsclesen', '15', 'textes', 'Keywords index', '', '', 'entries', '', '', '', '', '', '64', '', 'editable', '', '0', '', '32', '3', NOW()),
		(NULL, 'role', '0', 'entities_auteurs', 'Role dans l\'�laboration du document', '', 'role,.role', 'text', '', '*', '', '', 'xhtml:fontstyle;xhtml:phrase;xhtml:special', '64', '', 'editable', '', '0', '', '1', '7', NOW()),
		(NULL, 'email', '30', 'individus', 'Courriel', '', '', 'email', '', '*', '', '', '', '16', '', 'editable', '', '4', '', '1', '3', NOW()),
		(NULL, 'siteweb', '30', 'individus', 'Site web', '', '', 'url', '', '*', '', '', '', '16', '', 'editable', '', '0', '', '1', '4', NOW()),
		(NULL, 'description', '30', 'individus', 'Description', '', '', 'text', '', '*', '', '', 'xhtml:fontstyle;xhtml:phrase;xhtml:special;xhtml:block;Lien;Appel de Note', '16', '', 'fckeditor', 'Simple', '4', '', '1', '2', NOW()),
		(NULL, 'titreoeuvre', '17', 'textes', 'Titre de l\'oeuvre comment�e', '', 'titreoeuvre', 'text', '', '*', '', '', 'xhtml:fontstyle;xhtml:phrase;xhtml:special;Appel de Note', '64', '', 'display', '', '4', '', '32', '2', NOW()),
		(NULL, 'noticebibliooeuvre', '17', 'textes', 'Notice bibliographique de l\'oeuvre comment�e', '', 'noticebibliooeuvre', 'text', '', '*', '', '', 'xhtml:fontstyle;xhtml:phrase;xhtml:special;xhtml:block;Appel de Note', '64', '', 'display', '', '4', '', '32', '1', NOW()),
		(NULL, 'datepublicationoeuvre', '17', 'textes', 'Date de publication de l\'oeuvre comment�e', '', 'datepublioeuvre', 'tinytext', '', '*', '', '', '', '64', '', 'display', '', '4', '', '32', '70', NOW()),
		(NULL, 'auteuroeuvre', '17', 'textes', 'Auteur de l\'oeuvre comment�e', '', '', 'persons', '', '', '', '', '', '64', '', 'editable', '', '0', '', '32', '71', NOW()),
		(NULL, 'titre', '18', 'textessimples', 'Titre', '', '', 'tinytext', 'dc.title', '*', '', '', 'xhtml:fontstyle;xhtml:phrase;xhtml:special;xhtml:block;Lien;Appel de Note', '16', '', 'editable', '', '4', '', '32', '72', NOW()),
		(NULL, 'texte', '19', 'textessimples', 'Texte', '', '', 'text', '', '*', '', '', 'xhtml:fontstyle;xhtml:phrase;xhtml:special;xhtml:block;Lien;Appel de Note', '16', '', 'fckeditor', 'Simple', '4', '', '1', '73', NOW()),
		(NULL, 'ndla', '4', 'textes', 'Note de l\'auteur', '', 'ndla', 'text', '', '*', '', '', 'xhtml:fontstyle;xhtml:phrase;xhtml:special;xhtml:block;Lien;Appel de Note', '64', '', 'importable', '', '2', '', '32', '2', NOW()),
		(NULL, 'icone', '12', 'publications', 'Ic�ne de la publication', '', '', 'image', '', '*', '', '', '', '16', '', 'none', '', '0', '', '32', '1', NOW()),
		(NULL, 'icone', '3', 'textes', 'Ic�ne du document', '', '', 'image', '', '*', '', '', '', '64', '', 'none', '', '0', '', '32', '88', NOW()),
		(NULL, 'description', '8', 'fichiers', 'Description', '', '', 'text', '', '*', '', '', 'xhtml:fontstyle;xhtml:phrase;xhtml:special;xhtml:block;Lien;Appel de Note', '16', '', 'fckeditor', 'Simple', '4', '', '32', '2', NOW()),
		(NULL, 'alterfichier', '2', 'textes', 'Texte au format PDF', '', '', 'file', '', '*', '', '', '', '32', '', 'editable', '', '0', '', '32', '6', NOW()),
		(NULL, 'langue', '12', 'publications', 'Langue de la publication', '', '', 'lang', 'dc.language', '*', 'fr', '', '', '64', '', 'editable', '', '0', '', '32', '8', NOW()),
		(NULL, 'auteur', '24', 'fichiers', 'Auteur', '', '', 'persons', '', '', '', '', '', '64', '', 'editable', '', '0', '', '32', '91', NOW()),
		(NULL, 'auteur', '25', 'liens', 'Auteur de la notice d�crivant ce site', '', '', 'persons', '', '', '', '', '', '64', '', 'editable', '', '0', '', '32', '92', NOW()),
		(NULL, 'capturedecran', '6', 'liens', 'Capture d\'�cran du site', '', '', 'image', '', '*', '', '', '', '16', '', 'editable', '', '0', '', '32', '3', NOW()),
		(NULL, 'auteur', '26', 'textessimples', 'Auteur', '', '', 'persons', '', '', '', '', '', '64', '', 'editable', '', '0', '', '32', '93', NOW()),
		(NULL, 'numerodocument', '1', 'textes', 'Num�ro du document', '', 'numerodocument,numrodudocument', 'number', '', '*', '', '', '', '64', '', 'editable', '', '0', '', '32', '1', NOW()),
		(NULL, 'nom', '28', 'individus', 'Nom', '', '', 'tinytext', 'dc.title', '*', '', '', '', '16', '', 'editable', '', '4', '', '1', '1', NOW()),
		(NULL, 'prenom', '28', 'individus', 'Pr�nom', '', '', 'tinytext', '', '*', '', '', '', '16', '', 'editable', '', '4', '', '1', '2', NOW()),
		(NULL, 'accroche', '28', 'individus', 'Accroche', '', '', 'text', '', '*', '', '', 'xhtml:fontstyle;xhtml:phrase;xhtml:special;xhtml:block;Lien;Appel de Note', '16', '', 'fckeditor', 'Simple', '4', '', '1', '3', NOW()),
		(NULL, 'adresse', '30', 'individus', 'Adresse', '', '', 'text', '', '*', '', '', '', '16', '', 'editable', '3', '4', '', '1', '102', NOW()),
		(NULL, 'telephone', '30', 'individus', 'T�l�phone', '', '', 'tinytext', '', '*', '', '', '', '16', '', 'editable', '', '4', '', '1', '103', NOW()),
		(NULL, 'photographie', '28', 'individus', 'Photographie', '', '', 'image', '', '*', '', '', '', '16', '', 'editable', '', '0', '', '1', '104', NOW()),
		(NULL, 'vignette', '8', 'fichiers', 'Vignette', '', '', 'image', '', '*', '', '', '', '16', '', 'editable', '', '0', '', '32', '3', NOW()),
		(NULL, 'directeurdelapublication', '12', 'publications', 'Directeur de la publication', '', '', 'persons', '', '', '', '', '', '64', '', 'editable', '', '0', '', '1', '10', NOW()),
		(NULL, 'legende', '8', 'fichiers', 'L�gende', '', '', 'text', '', '*', '', '', 'xhtml:fontstyle;xhtml:phrase;xhtml:special;xhtml:block;Lien', '16', '', 'fckeditor', 'Basic', '4', '', '1', '4', NOW()),
		(NULL, 'credits', '24', 'fichiers', 'Cr�dits', '', '', 'tinytext', '', '*', '', '', '', '16', '', 'editable', '', '4', '', '1', '108', NOW()),
		(NULL, 'theme', '15', 'textes', 'Index th�matique', '', '', 'entries', '', '', '', '', '', '64', '', 'editable', '', '0', '', '1', '112', NOW()),
		(NULL, 'licence', '12', 'publications', 'Licence portant sur la publication', '', '', 'entries', '', '', '', '', '', '64', '', 'editable', '', '0', '', '1', '9', NOW()),
		(NULL, 'nom', '0', 'indexavances', 'D�nomination de l\'entr�e d\'index', '', '', 'tinytext', 'index key', '*', '', '', 'xhtml:fontstyle;xhtml:phrase;xhtml:special;xhtml:block', '16', '', 'editable', '', '4', '', '1', '113', NOW()),
		(NULL, 'description', '0', 'indexavances', 'Description de l\'entr�e d\'index', '', '', 'text', '', '*', '', '', 'xhtml:fontstyle;xhtml:phrase;xhtml:special;xhtml:block;Lien;Appel de Note', '16', '', 'fckeditor', 'Basic', '4', '', '1', '114', NOW()),
		(NULL, 'url', '0', 'indexavances', 'URL', '', '', 'url', '', '*', '', '', '', '16', '', 'editable', '', '0', '', '1', '115', NOW()),
		(NULL, 'icone', '0', 'indexavances', 'Ic�ne', '', '', 'image', '', '*', '', '', '', '16', '', 'editable', '', '0', '', '1', '116', NOW()),
		(NULL, 'licence', '3', 'textes', 'Licence portant sur le document', '', '', 'entries', '', '', '', '', '', '64', '', 'editable', '', '0', '', '1', '117', NOW()),
		(NULL, 'notefin', '2', 'textes', 'Notes de fin de document', '', 'notefin', 'text', '', '*', '', '', 'xhtml:fontstyle;xhtml:phrase;xhtml:special;xhtml:block;Lien', '32', '', 'importable', '', '4', '', '32', '3', NOW()),
		(NULL, 'altertitre', '10', 'publications', 'Titre alternatif de la publication (dans une autre langue)', '', 'titretraduitfr:fr,titretraduiten:en,titretraduites:es,titretraduitpt:pt,titretraduitit:it,titretraduitde:de,titretraduitru:ru,titleen:en', 'mltext', '', '*', '', '', 'xhtml:fontstyle;xhtml:phrase;xhtml:special;xhtml:block;Appel de Note', '32', '', 'editable', '', '4', '', '1', '120', NOW()),
		(NULL, 'motscleses', '15', 'textes', 'Palabras claves', '', '', 'entries', '', '', '', '', '', '64', '', 'editable', '', '0', '', '1', '121', NOW()),
		(NULL, 'motsclede', '15', 'textes', 'Schlagworter', '', '', 'entries', '', '', '', '', '', '64', '', 'editable', '', '0', '', '1', '122', NOW()),
		(NULL, 'urlpublicationediteur', '13', 'publications', 'Voir sur le site de l\'�diteur', '', '', 'url', '', '*', '', '', '', '32', '', 'editable', '', '0', '', '1', '123', NOW()),
		(NULL, 'nombremaxitems', '6', 'liens', 'Nombre maximum d\'items du flux', '', '', 'int', '', '*', '', '', '', '16', '', 'editable', '', '0', '', '32', '124', NOW()),
		(NULL, 'descriptionouvrage', '12', 'publications', 'Description physique de l\'ouvrage', '', '', 'text', '', '*', '', '', '', '64', '', 'editable', '', '0', '', '32', '125', NOW()),
		(NULL, 'erratum', '11', 'publications', 'Erratum', '', '', 'text', '', '*', '', '', '', '64', '', 'editable', '', '0', '', '32', '126', NOW()),
		(NULL, 'site', '0', 'entities_auteurs', 'Site', '', 'site, .site', 'url', '', '*', '', '', '', '32', '', 'editable', '', '4', '', '1', '6', NOW());");
		$this->requetes .= $q;
		if(!mysql_query($q)) {
			return mysql_error();
		}
		$query .= "UPDATE ".$GLOBALS['tp']."entities SET idtype = (SELECT id FROM ".$GLOBALS['tp']."types WHERE type = 'fichierannexe') WHERE idtype = (SELECT id from ".$GLOBALS['tp']."types WHERE type = 'documentannexe-lienfichier');";
		$query .= "DELETE FROM ".$GLOBALS['tp']."types WHERE type = 'documentannexe-lienfichier';";


		if ($err = $this->__mysql_query_cmds($query)) {
				return $err;
		} else {
			// index
			$i = 1;
			if(!$result = mysql_query("SELECT id, langue, nom FROM $GLOBALS[tp]entrees__old;")) {
				return mysql_error();
			}
			while ($row = mysql_fetch_assoc($result)) {
				unset($q);
				if($row['langue'] == 'fr') {
					$q = "SELECT id FROM ".$GLOBALS['tp']."entrytypes WHERE type = 'motcle';";
				} elseif($row['langue'] == 'en') {
					$q = "SELECT id FROM ".$GLOBALS['tp']."entrytypes WHERE type = 'motsclesen';";
				} elseif($row['langue'] == 'de') {
					$q = "SELECT id FROM ".$GLOBALS['tp']."entrytypes WHERE type = 'motsclede';";
				} elseif($row['langue'] == 'es') {
					$q = "SELECT id FROM ".$GLOBALS['tp']."entrytypes WHERE type = 'motscleses';";
				} else {
					$q = "SELECT id FROM ".$GLOBALS['tp']."entrytypes WHERE type = 'motcle';";
				}
				$this->requetes .= $q;
				if(!$resu = mysql_query($q)) {
					return mysql_error();
				}
				while ($rows = mysql_fetch_array($resu)) {
					$q = "UPDATE ".$GLOBALS['tp']."entries SET idtype = '".$rows['id']."', rank = '".$i."', sortkey = \"".strtolower(trim($row['nom']))."\" WHERE id = " . $row['id'] . ';';
					$this->requetes .= $q;
					if(!mysql_query($q)) {
						return mysql_error();
					}
				}
				$i++;
			}
			mysql_free_result($result);

			// maj identifier pour affichage correct des champs auteur lors de l'�dition d'une entit�
			// sans �a, seul les champs nom/pr�nom sont affich�s
			if(!$result = mysql_query("SELECT id, g_title, identifier FROM $GLOBALS[tp]entities")) {
				return mysql_error();
			}
			while($res = mysql_fetch_array($result)) {
				if($res['identifier'] == "") {
					$identifier = preg_replace(array("/\W+/", "/-+$/"), array('-', ''), makeSortKey(strip_tags($res['g_title'])));
					$q = "UPDATE $GLOBALS[tp]entities SET identifier = '".$identifier."' WHERE id = '".$res['id']."';";
					$this->requetes .= $q;
					if(!mysql_query($q)) {
						return mysql_error();
					}
				}
			}
			mysql_free_result($result);
// 			echo '<p>update_ME ok</p>';
			return "Ok";
		}

	}


	/**
	 * Execute une ou plusieurs commandes Mysql
	 */

	private function __mysql_query_cmds($cmds, $table = '') {
		$sql = str_replace('_PREFIXTABLE_', $GLOBALS['tp'], $cmds);
		$sql = str_replace('#_TP_', $GLOBALS['tp'], $sql);

		//$charset
		$sql = str_replace('_CHARSET_', '', $sql);
		
		if (!$sql) {
			$err = 'Pb pour ex�cuter la commande suivante : ' . $cmds;
			return $err;
		}
	
		$request = preg_split ("/;/", preg_replace("/#.*?$/m","",$sql));
		if ($table) { // select the commands operating on the table  $table
			$request = preg_grep("/(REPLACE|INSERT)\s+INTO\s+$GLOBALS[tp]$table\s/i",$request);
		}
		if (!$request) {
			$err = 'Pb pour ex�cuter la commande suivante : ' . $request;
			return $err;
		}

		foreach ($request as $cmd) {//echo $cmd . '<p>';
			$cmd=trim(preg_replace ("/^#.*?$/m","",$cmd));
			if ($cmd) {	
 				if(preg_match("/(REPLACE|INSERT|UPDATE)/i", $cmd)) 
 					$cmd = utf8_encode($cmd);

				$this->requetes .= $cmd;
				if (!mysql_query($cmd)) {
					$err = '<font COLOR=red>' . mysql_error() . '<p>requete : ' . $cmd . '</font><br>';
					return $err; // sort, ca sert a rien de continuer
      				}
    			}
  		}
		return false;
	}

	/**
	 * G�re la migration des documentannexes dans les tables respectives
	 */
	public function cp_docs07_to_08()
	{
		$query_select = "SELECT 
					".$GLOBALS['tp']."entites__old.*,
					".$GLOBALS['tp']."documents__old.*,
					".$GLOBALS['tp']."types__old.type,
					".$GLOBALS['tp']."types__old.classe
				FROM 
					".$GLOBALS['tp']."types__old,
					".$GLOBALS['tp']."entites__old, 
					".$GLOBALS['tp']."documents__old 
				WHERE 
					type LIKE 'documentannexe-%'
					AND ".$GLOBALS['tp']."documents__old.identite=entites__old.id 
					AND ".$GLOBALS['tp']."entites__old.idtype=types__old.id;";
		if(!$req = mysql_query($query_select)) {
			return mysql_error();
		}
		while($res = mysql_fetch_array($req)) {
			if($res['type'] != "documentannexe-lienfacsimile" && $res['type'] != "documentannexe-lienfichier") {
				$query .= "INSERT INTO ".$GLOBALS['tp']."liens (identity, titre, url) VALUES ('".$res['id']."', \"".str_replace('"', "'", $res['titre'])."\", \"".$res['lien']."\");";
			} elseif($res['type'] == "documentannexe-lienfacsimile") {
				$query .= "UPDATE ".$GLOBALS['tp']."documents SET alterfichier = \"".$res['lien']."\" WHERE identity = '".$res['idparent']."';";
			} elseif($res['type'] == "documentannexe-lienfichier") {
				$query .= "INSERT INTO ".$GLOBALS['tp']."fichiers (identity, titre, document) VALUES ('".$res['id']."', \"".str_replace('"', "'", $res['titre'])."\", \"".$res['lien']."\");";
			}
		}

		if ($err = $this->__mysql_query_cmds($query)) {
				return $err;
		} else {
// 			echo '<p>update_docannexes ok</p>';
			return "Ok";
		}
	}

	/**
	 * Dump de la base avant modifs
	 */	
	public function dump_before_changes()
	{
		global $home, $site, $zipcmd; 
		require($home."backupfunc.php");
		
		$outfile="site-$site.sql";
		$tmpdir=tmpdir();
		mysql_dump($GLOBALS['currentdb'],$GLOBALS['lodelsitetables'],$tmpdir."/".$outfile);
		# verifie que le fichier n'est pas vide
		if (filesize($tmpdir."/".$outfile)<=0) return "ERROR: mysql_dump failed";
		
		// tar les sites et ajoute la base
		$archivetmp=tempnam($tmpdir,"lodeldump_").".zip";
		$archivefilename="site-$site-".date("dmy").".zip";
		if ($zipcmd && $zipcmd!="pclzip") {
			system($zipcmd." -q $archivetmp -j $tmpdir/$outfile");
		} else { // pclzip
			require($home."pclzip.lib.php");
			$archive=new PclZip ($archivetmp);
			$archive->create($tmpdir."/".$outfile,PCLZIP_OPT_REMOVE_ALL_PATH);
		} // end of pclzip option
		
		if (!file_exists($archivetmp)) return "ERROR: the zip command or library does not produce any output";
		@unlink($tmpdir."/".$outfile); // delete the sql file

		if (operation("download",$archivetmp,$archivefilename,$context)) 
			return "Ok";		
	}

	/**
	 * Dump de la base avant modifs
	 */	
	public function dump_after_changes_to08()
	{
		global $home, $site, $zipcmd; 
		require($home."backupfunc.php");
		
		$outfile="site-$site.sql";
		$tmpdir=tmpdir();
		mysql_dump($GLOBALS['currentdb'],$GLOBALS['lodelsitetables'],$tmpdir."/".$outfile);
		# verifie que le fichier n'est pas vide
		if (filesize($tmpdir."/".$outfile)<=0) return "ERROR: mysql_dump failed";
		
		// tar les sites et ajoute la base
		$archivetmp=tempnam($tmpdir,"lodeldump_").".zip";
		$archivefilename="site08-$site-".date("dmy").".zip";
		if ($zipcmd && $zipcmd!="pclzip") {
			system($zipcmd." -q $archivetmp -j $tmpdir/$outfile");
		} else { // pclzip
			require($home."pclzip.lib.php");
			$archive=new PclZip ($archivetmp);
			$archive->create($tmpdir."/".$outfile,PCLZIP_OPT_REMOVE_ALL_PATH);
		} // end of pclzip option
		
		if (!file_exists($archivetmp)) return "ERROR: the zip command or library does not produce any output";
		@unlink($tmpdir."/".$outfile); // delete the sql file

		if (operation("download",$archivetmp,$archivefilename,$context)) 
			return "Ok";		
	}

	/**
	 * Dump des modifs effectu�es sur la base
	 */	
	public function dump_changes_to08()
	{
		global $home, $site, $zipcmd; 
		require($home."backupfunc.php");
		
		$outfile="site-$site-changesto08.sql";
		$tmpdir=tmpdir();
		$f = fopen($tmpdir."/".$outfile, "w");
		fwrite($f, $this->requetes);
		fclose($f);
		# verifie que le fichier n'est pas vide
		if (filesize($tmpdir."/".$outfile)<=0) return "ERROR: dumping changes failed";
		
		// tar les sites et ajoute la base
		$archivetmp=tempnam($tmpdir,"lodeldump_").".zip";
		$archivefilename="siteto08-$site-".date("dmy").".zip";
		if ($zipcmd && $zipcmd!="pclzip") {
			system($zipcmd." -q $archivetmp -j $tmpdir/$outfile");
		} else { // pclzip
			require($home."pclzip.lib.php");
			$archive=new PclZip ($archivetmp);
			$archive->create($tmpdir."/".$outfile,PCLZIP_OPT_REMOVE_ALL_PATH);
		} // end of pclzip option
		
		if (!file_exists($archivetmp)) return "ERROR: the zip command or library does not produce any output";

		if (operation("download",$archivetmp,$archivefilename,$context)) 
			return "Ok";		
	}

	/**
	* Copie les fichiers contenus dans $source vers $target
	* @param string $source 
	* @param string $target 
	*/	
	public function datas_copy( $source, $target )
	{
		if ( is_dir( $source ) )
		{
			@mkdir( $target );
			
			$d = dir( $source );
			
			while ( FALSE !== ( $entry = $d->read() ) )
			{
				if ( $entry == '.' || $entry == '..' )
				{
					continue;
				}
			
				$Entry = $source . '/' . $entry;           
				if ( is_dir( $Entry ) )
				{
					$this->datas_copy( $Entry, $target . '/' . $entry );
					continue;
				}

				copy( $Entry, $target . '/' . $entry );
			}
			
			$d->close();
		} else {
			copy( $source, $target );
		}
		return "Ok";
	}

	public function update_tpl($target)
	{
		global $site;
		$query = "INSERT INTO _PREFIXTABLE_translations (id, lang, title, textgroups, translators, modificationdate, creationdate, rank, status, upd) VALUES ('1', 'FR', 'Fran�ais', 'site', '', '', NOW(), '1', '1', NOW());";
	
		$query .= "UPDATE _PREFIXTABLE_texts SET lang = 'FR', textgroup = 'site';
		UPDATE _PREFIXTABLE_objects SET class='persons' WHERE class='personnes';
		UPDATE _PREFIXTABLE_objects SET class='entrytypes' WHERE class='typeentrees';
		UPDATE _PREFIXTABLE_objects SET class='persontypes' WHERE class='typepersonnes';";
/*		if ($err = $this->__mysql_query_cmds($query)) {
				return $err;
		}	
*/
		// ce qu'on cherche � remplacer
		$lookfor = array("#NOTEBASPAGE",
				 "textes",
				 "documents",
				 "objets",
				 "entites.ordre",
				 "entites",
				 "champs",
				 "groupesdechamps",
				 "personnes",
				 "groupes",
				 "users_groupes",
				 "typespersonnes",
				 "typeentrees",
				 "taches",
				 "typeentites_typeentites",
				 "typeentites_typepersonnes",
				 "typeentites_typeentrees",
				 "entites_personnes",
				 "entites_entrees",
				 "WHERE=\"rep='",
				 "statut",
				 "identifiant",
				 "ordre",
				 "degres",
				 "identite",
				 "auteurs\.",
				 "entrees",
				 "entries\.",
				 "entrees\.id",
				 "directeur de publication"
				 );
		// et ce qu'on met � remplacer
		$replace = array("#NOTESBASPAGE",
				"texts",
				"textes",
				"objects",
				"entities.rank",
				"entities",
				"tablefields",
				"tablefieldgroups",
				"auteurs",
				"usergroups",
				"users_usergroups",
				"persontypes",
				"entrytypes",
				"tasks",
				"entitytypes_entitytypes",
				"entitytypes_persontypes",
				"entitytypes_entrytypes",
				"entities_persons",
				"entities_entries",
				"WHERE=\"name='",
				"status",
				"identifier",
				"'order'",
				"degree",
				"identity",
				"personnes.",
				"entries",
				"entrees.",
				"entries.id",
				"directeurdelapublication"
				);
		// variable de travail : on fait deux tours : le premier pour r�cup�rer le nom de toutes les macros/fonctions pr�sentes dans le r�pertoire source et target
		// le second pour travailler :)
		$i = 0; 
		// tableau des noms de macros/fonctions 0.7
		$funclist = array();
		// tableau des noms des macros/fonctions ayant un nom identique en 0.7/0.8
		$funcNamesToChange = array();
		// tableau des macros en double
		$funcToAdd = array();	
		// liste des fichiers macros de la 0.7 � ajouter dans les tpl de la 0.8
		$upMacroFile = '<USE MACROFILE="macros.html" />
				<USE MACROFILE="macros_admin.html" />
				<USE MACROFILE="macros_affichage.html" />
				<USE MACROFILE="macros_technique.html" />
				<USE MACROFILE="macros_images.html" />
				<USE MACROFILE="macros_navigation.html" />
				<USE MACROFILE="macros_presentation.html" />
				<USE MACROFILE="macros_site.html" />';

		// c'est parti on traite tous les templates et fichiers de macros contenus dans les r�pertoires tpl
		if (is_dir("tpl")) {
			while($i < 2) {
				if($i === 0) {
					$i++;
					if ($dh = opendir("tpl")) {
						while (($file = readdir($dh)) !== false) {
							unset($tmp);
							unset($defins);
							// est-ce bien un fichier de macros ? extension html obligatoire et 'macros' dans le nom
							if("html" === substr($file, -4, 4) && !is_link("tpl/".$file) && !preg_match("/oai/", $file)) {
								if(preg_match("`macros`i", $file)) {	
									$tmp = file_get_contents("tpl/".$file);
									preg_match_all("`<(DEFMACRO|DEFFUNC) NAME=\"([^\"]*)\"[^>]*>(.*)</(DEFMACRO|DEFFUNC)>`iUs", $tmp, $defins);
									// on r�cup�re le nom des macros/fonctions de la 0.7
									$funclist = array_merge($funclist, $defins[2]);
								}
							}
						}
						closedir($dh);
					} else {
						return "ERROR : cannot open directory 'tpl'.";
					}

					$funclist = array_unique($funclist);

					if ($dh = opendir($target."/tpl")) {
						while (($file = readdir($dh)) !== false) {
							unset($tmp);
							unset($defins);
							unset($defin);
							unset($def);
							// est-ce bien un fichier de tpl/macros ? extension html obligatoire et/ou 'macros' dans le nom
							if("html" === substr($file, -4, 4) && !is_link($target."/tpl/".$file) && !preg_match("/oai/", $file)) {
								$tmp = file_get_contents($target."/tpl/".$file);
								if(preg_match_all("`<(DEFMACRO|DEFFUNC) NAME=\"([^\"]*)\"[^>]*>(.*)</(DEFMACRO|DEFFUNC)>`iUs", $tmp, $defins)) {
									$defins[2] = array_unique($defins[2]);
									// on r�cup�re les macros/fonctions en double
									foreach($defins[2] as $k=>$def) {
										if(!in_array($def, $funclist)) {
											//$funcNamesToChange[] = $def;
											$funcToAdd[$file][] = $defins[0][$k];
										}
									}
								}
// 								unset($def);
// 								// on r�cup�re les noms des macros 0.7 utilis�es en 0.8 et on les renomme
// 								preg_match_all("`<(MACRO|FUNC) NAME=\"([^\"]*)\"`iU", $tmp, $defin);
// 								$defin[2] = array_unique($defin[2]);
// 								foreach($defin[2] as $k=>$def) {
// 									if(in_array($def, $funclist)) {
// 										$tmp = str_replace('"'.$def.'"', '"'.$def.'07"', $tmp);
// 									}
// 								}
								if(!file_exists("tpl/".$file)) {
									$tmp = strtr($tmp, array("\n<USE MACROFILE=\"macros_site.html\">\n"=>$upMacroFile,"\n<USE MACROFILE=\"macros_site.html\" />\n"=>$upMacroFile));
									$tmp = strtr($tmp, array("<MACRO NAME=\"FERMER_HTML\" />"=>"<MACRO NAME=\"FERMER_HTML08\" />", "<MACRO NAME=\"FERMER_HTML\">"=>"<MACRO NAME=\"FERMER_HTML08\" />"));
								}
								$f = fopen($target."/tpl/".$file, "w");
								fwrite($f, $tmp);
								fclose($f);
							}
						}
						closedir($dh);
						//$funcNamesToChange = array_unique($funcNamesToChange);
					} else {
						return "ERROR : cannot open directory 'tpl'.";
					}
				} elseif($i === 1) {
					$i++;
					if ($dh = opendir("tpl")) {
						while (($file = readdir($dh)) !== false) {
							unset($tmpFile);
							unset($tmp2);
							unset($defs);
							unset($def);
							unset($fntc);
							// est-ce bien un template ou un fichier de macros ? extension html obligatoire
							if("html" === substr($file, -4, 4) && !is_link("tpl/".$file) && !preg_match("/oai/", $file)) {
								$tmpFile = file_get_contents("tpl/".$file);
// 								if(file_exists($target."/tpl/".$file)) {
// 								// cas sp�cial des macros
// 								// il faut avoir la possibilit� d'utiliser les macros pr�sentes en 0.8
// 								// ET celles utilis�es auparavant dans la 0.7
// 								// on merge les deux fichiers
// 									$tmp2 = file_get_contents($target."/tpl/".$file);
// 									preg_match_all("`<(DEFMACRO|DEFFUNC) NAME=\"([^\"]*)\"[^>]*>(.*)</(DEFMACRO|DEFFUNC)>`iUs", $tmp2, $defs);
// 									$defs[2] = array_unique($defs[2]);
// 									foreach($defs[2] as $k=>$def) {
// 										// on v�rifie que la macro en cours n'existe pas d�j� sinon on l'ajoute
// 										// si elle existe on va renommer la macro 0.7 avec le suffixe 07
// 										if(!in_array($def, $funclist)) {
// 											$tmpFile .= "\n\n".$defs[0][$k];
// 										}
// 									}
// 								}
//  								// on renomme les macros existantes en 0.7 et ayant un double en 0.8 avec un suffixe '07'
// 								foreach($funcNamesToChange as $fntc) {
// 									$tmpFile = str_ireplace("\"".$fntc."\"", "\"".$fntc.'07"', $tmpFile);
// 								}

								// on ajoute les macros de la 0.8 dans les tpl de la 0.7
								if(!empty($funcToAdd[$file])) {
									foreach($funcToAdd[$file] as $fcta) {
										$tmpFile .= "\n\n".$fcta;
									}
								}	
			
								// on cherche dans chaque tpl et on remplace par l'�quivalent 0.8
 								foreach($lookfor as $k=>$look) {
 									$tmpFile = str_ireplace($look, $replace[$k], $tmpFile);
 								}

								// ajustement pr�cis
								if($file == "barre.html" || $file == "macros_presentation.html") {
									$tmpFile = strtr($tmpFile, array("[#TITRE]"=>"[#TITLE]", "[#NOM]"=>"[#TITLE]"));	
								} elseif($file == "macros_site.html") {
									$tmpFile = strtr($tmpFile, array("entriesALPHABETIQUES"=>"ENTREESALPHABETIQUES", "entriesRECURSIF"=>"ENTREESRECURSIF", "entriesauteurs"=>"ENTREESPERSONNES"));
									$tmpFile .= '\n\n<DEFMACRO NAME="FERMER_HTML08">
													</body>
												</html>
											</DEFMACRO>';
								}
								// on met en majuscule ce qui doit l'�tre
								// cad variables lodel et nom des macros
								$tmpFile = preg_replace_callback("`\[\#[^\]]*\]`", create_function('$matches','return strtoupper($matches[0]);'), $tmpFile);
								$tmpFile = preg_replace_callback("`MACRO NAME=\"([^\"]*)\"`", create_function('$matches','return strtoupper($matches[0]);'), $tmpFile);
								// on �crit le fichier
								$f = fopen($target."/tpl/".$file, "w");
								fwrite($f, $tmpFile);
								fclose($f);
							}
						}
						closedir($dh);
					} else {
						return "ERROR : cannot open directory 'tpl'.";
					}
				}
			}

			// on cr�e des liens symboliques pointant vers index.php pour simuler les scripts document.php, sommaire.php, etc ..
			symlink("index.".$GLOBALS['extensionscripts'], $target."/document.".$GLOBALS['extensionscripts']);
			symlink("index.".$GLOBALS['extensionscripts'], $target."/sommaire.".$GLOBALS['extensionscripts']);
			symlink("index.".$GLOBALS['extensionscripts'], $target."/personnes.".$GLOBALS['extensionscripts']);
			symlink("index.".$GLOBALS['extensionscripts'], $target."/entrees.".$GLOBALS['extensionscripts']);
			symlink("index.".$GLOBALS['extensionscripts'], $target."/entree.".$GLOBALS['extensionscripts']);
			symlink("index.".$GLOBALS['extensionscripts'], $target."/docannexe.".$GLOBALS['extensionscripts']);
		} else {
			return "ERROR : directory 'tpl' is missing.";
		}
		return "Ok";
	}

}


?>