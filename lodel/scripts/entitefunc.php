<?php
/*
 *
 *  LODEL - Logiciel d'Edition ELectronique.
 *
 *  Copyright (c) 2001-2002, Ghislain Picard, Marin Dacos
 *  Copyright (c) 2003, Ghislain Picard, Marin Dacos, Luc Santeramo, Nicolas Nutten, Anne Gentil-Beccot
 *  Copyright (c) 2004, Ghislain Picard, Marin Dacos, Luc Santeramo, Anne Gentil-Beccot, Bruno C�nou
 *
 *  Home page: http://www.lodel.org
 *
 *  E-Mail: lodel@lodel.org
 *
 *                            All Rights Reserved
 *
 *     This program is free software; you can redistribute it and/or modify
 *     it under the terms of the GNU General Public License as published by
 *     the Free Software Foundation; either version 2 of the License, or
 *     (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU General Public License for more details.
 *
 *     You should have received a copy of the GNU General Public License
 *     along with this program; if not, write to the Free Software
 *     Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.*/



//
// fonction qui renvoie les valeures perennes: statut, groupe, ordre, iduser
//

function get_variables_perennes($context,$critere) 

{
    $groupe= ($droitadmin && $context[groupe]) ? intval($context[groupe]) : "groupe";

    $result=mysql_query("SELECT ordre,$groupe,statut,iduser FROM $GLOBALS[tp]entites WHERE $critere") or die (mysql_error());
    if (!mysql_num_rows($result)) { die ("vous n'avez pas les droits: get_variables_perennes"); }
    return mysql_fetch_row($result);
    // renvoie l'ordre, le groupe, le statut
}


//
// fonction qui retourne le statut d'une entite
//

function get_statut($id) 

{
    $result=mysql_query("SELECT statut FROM $GLOBALS[tp]entites WHERE id='$id'") or die (mysql_error());
    if (!mysql_num_rows($result)) die ("l'entites '$id' n'existe pas");
    list ($statut)=mysql_fetch_row($result);
    return $statut;
    // renvoie l'ordre, le groupe, le statut
}


//
// fonction qui renvoie le groupe d'une entite.
//

function get_groupe($context,$idparent)

{
  global $droitadmin,$usergroupes;

  // cherche le groupe et les droits
  if ($droitadmin) { // on prend celui qu'on nous donne
    $groupe=intval($context[groupe]); if (!$groupe) $groupe=1;

  } elseif ($idparent) { // on prend celui du idparent
    $result=mysql_query("SELECT groupe FROM $GLOBALS[tp]entites WHERE id='$idparent' AND groupe IN ($usergroupes)") or die (mysql_error());
    if (!mysql_num_rows($result)) 	die("vous n'avez pas les droits: sortie 2");
    list($groupe)=mysql_fetch_row($result);
  } else {
    die("vous n'avez pas les droits: sortie 3");
  }
  return $groupe;
}


//
// gere la creation et la modification d'une entite
//

function enregistre_entite (&$context,$id,$classe,$champcritere="",$returnonerror=TRUE) 

{
  global $home,$droitadmin,$usergroupes;

  $iduser= $GLOBALS[droitadminlodel] ? 0 : $GLOBALS[iduser];

  $entite=& $context[entite];
  $context[idtype]=intval($context[idtype]);
  $id=intval($context[id]);
  $idparent=intval($context[idparent]);

#  print_r($context);
  //
  // check we have the right to add such an entite
  //
  if ($id>0) {
    $result=mysql_query("SELECT idparent,idtype FROM $GLOBALS[tp]entites WHERE id='$id'") or die(mysql_error());
    list($idparent,$context[idtype])=mysql_fetch_row($result);
  }
  if ($idparent>0) {
    $result=mysql_query("SELECT cond FROM $GLOBALS[tp]typeentites_typeentites,$GLOBALS[tp]entites WHERE id='$idparent' AND idtypeentite2=idtype AND idtypeentite='$context[idtype]'") or die(mysql_error());
  } else {
    $result=mysql_query("SELECT cond FROM $GLOBALS[tp]typeentites_typeentites WHERE idtypeentite2=0 AND idtypeentite='$context[idtype]'") or die(mysql_error());

  }
  if (mysql_num_rows($result)<=0) die("ERROR: Entities of type $context[idtype] are not allowed in entity $idparent");
  //
  // ok, we are allowed to modifiy/add this entity.
  //

  // for new entite, all the field must be set with the defaut value if any.
  // for the old entite, the criteria must be true to modifie the field.
  if ($champcritere && $id) $extrawhere=" AND ".$champcritere;
  if ($champcritere) $champcritere=",(".$champcritere.")";

  // check for errors and build the set
  $sets=array();
  require_once ($home."connect.php");
  require_once ($home."champfunc.php");

  // file to move once the document id is know.
  $files_to_move=array();
  

  $result=mysql_query("SELECT $GLOBALS[tp]champs.nom,type,cond,defaut,balises $champcritere FROM $GLOBALS[tp]champs,$GLOBALS[tp]groupesdechamps WHERE idgroupe=$GLOBALS[tp]groupesdechamps.id AND classe='$classe' AND $GLOBALS[tp]champs.statut>0 AND $GLOBALS[tp]groupesdechamps.statut>0 $extrawhere") or die (mysql_error());
  while (list($nom,$type,$cond,$defaut,$balises,$critereok)=mysql_fetch_row($result)) {
    require_once($home."textfunc.php");
    // check if the field is required or not, and rise an error if any problem.

    if ( !$champcritere || $critereok) {
      if ($cond=="+" && !trim($entite[$nom])) $erreur[$nom]="+";
    } else {
      $entite[$nom]="";
    }

    // clean automatically the fields when required.
    if (!is_array($entite[$nom]) && trim($entite[$nom]) && in_array($type,$GLOBALS[type_autostriptags])) $entite[$nom]=trim(strip_tags($entite[$nom]));
    // special processing depending on the type.
    switch ($type) {
    case "date" :
    case "datetime" :
    case "time" :
      include_once($home."date.php");
      if ($entite[$nom]) {
	$entite[$nom]=mysqldatetime($entite[$nom],$type);
	if (!$entite[$nom]) $erreur[$nom]=$type;
      } elseif ($defaut) {
	$dt=mysqldatetime($defaut,$type);
	if ($dt) {
	  $entite[$nom]=$dt;
	} else {
	  die("valeur par defaut non reconnue: \"$defaut\"");
	}
      }
      break;
    case "int" :
      if ((!isset($entite[$nom]) || $entite[$nom]==="") && $defaut!=="") $entite[$nom]=intval($defaut);
      if (isset($entite[$nom]) && 
	  (!is_numeric($entite[$nom]) || intval($entite[$nom])!=$entite[$nom])) 
	$erreur[$nom]="int";
      break;
    case "number" : 
      if ((!isset($entite[$nom]) || $entite[$nom]==="") && $defaut!=="") $entite[$nom]=doubleval($defaut);
      if (isset($entite[$nom]) && 
			!is_numeric($entite[$nom])) $erreur[$nom]="numeric";
      break;
    case "email" : 
      if (!$entite[$nom] && $defaut) $entite[$nom]=$defaut;
      if ($entite[$nom]) {
#      $validchar='-!#$%&\'*+\\\\\/0-9=?A-Z^_`a-z{|}~';
	$validchar='-0-9A-Z_a-z';
	if (!preg_match("/^[$validchar]+@([$validchar]+\.)+[$validchar]+$/",$entite[$nom])) $erreur[$nom]="url";
      }
      break;
    case "url" : 
      if (!$entite[$nom] && $defaut) $entite[$nom]=$defaut;
      if ($entite[$nom]) {
#      $validchar='-!#$%&\'*+\\\\\/0-9=?A-Z^_`a-z{|}~';
	$validchar='-0-9A-Z_a-z';
	if (!preg_match("/^(http|ftp):\/\/([$validchar]+\.)+[$validchar]+/",$entite[$nom])) $erreur[$nom]="url";
      }
      break;
    case "boolean" :
      $entite[$nom]=$entite[$nom] ? 1 : 0;
      break;
    case "mltext" :
      if (is_array($entite[$nom])) {
	$str="";
	foreach($entite[$nom] as $lang=>$value) {
	  $value=lodel_strip_tags(trim($value),$balises);
	  if ($value) $str.="<r2r:ml lang=\"$lang\">$value</r2r:ml>";
	}
	$entite[$nom]=$str;
      }
      break;
    case 'image' :
    case 'fichier' :
      if ($context['erreur'][$nom]) {  $erreur[$nom]=$context['erreur'][$nom]; break; } // erreur has been already detected
      if (is_array($entite[$nom])) unset($entite[$nom]);
      if (!$entite[$nom] || $entite[$nom]=="none") break;
      // check for a hack or a bug
      $lodelsource='lodel\/sources';
      $docannexe='docannexe\/'.$type.'\/([^\.\/]+)';
      if (!preg_match("/^(?:$lodelsource|$docannexe)\/[^\/]+$/",$entite[$nom],$dirresult)) die("ERROR: bad filename in $nom \"$entite[$nom]\"");

      // if the filename is not "temporary", there is nothing to do
      if (!preg_match("/^tmpdir-\d+$/",$dirresult[1])) break;
      // add this file to the file to move.
      $files_to_move[$nom]=array(filename=>$entite[$nom],type=>$type,name=>$nom);
      #unset($entite[$nom]); // it must not be update... the old files must be remove later (once everything is checked)
      break;
    default :
      if (isset($entite[$nom])) $entite[$nom]=lodel_strip_tags($entite[$nom],$balises);
      // recheck entite is still not empty
      if (!isset($entite[$nom])) $entite[$nom]=lodel_strip_tags($defaut,$balises);
    }
    if (isset($entite[$nom])) {
      $sets[$nom]="'".addslashes(stripslashes($entite[$nom]))."'"; // this is for security reason, only the authorized $nom are copied into sets. Add also the quote.
    }
  } // end of while over the results

  if ($erreur) { 
    $context['erreur']=$erreur;
    if ($returnonerror) return FALSE;
  }

  lock_write($classe,"objets","entites","relations",
	     "entites_personnes","personnes",
	     "entites_entrees","entrees","typeentrees","types");

  if ($id>0) { // UPDATE
    if ($id>0 && !$GLOBALS[droitadmin]) {
      // verifie que le document est editable par cette personne
      $result=mysql_query("SELECT id FROM  $GLOBALS[tp]entites WHERE id='$id' AND groupe IN ($usergroupes)") or die(mysql_error());
      if (!mysql_num_rows($result)) die("vous n'avez pas les droits. Erreur dans l'interface");
    }
    // change group ?
    $groupeset= ($droitadmin && $context[groupe]) ? ", groupe=".intval($context[groupe]) : "";
    // change type ?
    $typeset=$context[idtype] ? ",idtype='$context[idtype]'" : "";
    // change statut ?
    $statut=get_statut($id);
    if ($statut<=-64 && $context[statut]) {
      $statut=intval($context[statut]);
      $statutset=",statut='$statut' ";
    }
    mysql_query("UPDATE $GLOBALS[tp]entites SET identifiant='$context[identifiant]' $typeset $groupeset $statutset WHERE id='$id'") or die(mysql_error());
    if ($grouperec && $droitadmin) change_groupe_rec($id,$groupe);

    move_files($id,$files_to_move,$sets);

    foreach ($sets as $nom=>$value) { $sets[$nom]=$nom."=".$value; }
    if ($sets) mysql_query("UPDATE $GLOBALS[tp]$classe SET ".join(",",$sets)." WHERE identite='$id'") or die (mysql_error());

  } else { // INSERT
    require_once($home."entitefunc.php");
    // cherche le groupe et les droits
    $groupe=get_groupe($context,$idparent);
    // cherche l'ordre
    $ordre=get_ordre_max("entites","idparent='$idparent'");
    $statut=$context[statut] ? intval($context[statut]) : -1; // non publie par defaut
    if (!$context[idtype]) { // prend le premier venu
      $result=mysql_query("SELECT id FROM $GLOBALS[tp]types WHERE classe='$classe' AND statut>0 ORDER BY ordre LIMIT 0,1") or die(mysql_error());
      if (!mysql_num_rows($result)) die("pas de type valide ?");
      list($context[idtype])=mysql_fetch_row($result);
    }
    $id=uniqueid($classe);
    mysql_query("INSERT INTO $GLOBALS[tp]entites (id,idparent,idtype,identifiant,ordre,statut,groupe,iduser) VALUES ('$id','$idparent','$context[idtype]','$context[identifiant]','$ordre','$statut','$groupe','$iduser')") or die (mysql_error());

    require_once($home."managedb.php");
    creeparente($id,$context[idparent],FALSE);
    move_files($id,$files_to_move,$sets);

    $sets[identite]="'$id'";
    mysql_query("INSERT INTO $GLOBALS[tp]$classe (".join(",",array_keys($sets)).") VALUES (".join(",",$sets).")") or die (mysql_error());
  }  

  enregistre_personnes($context,$id,$statut,FALSE);
  enregistre_entrees($context,$id,$statut,FALSE);

  if ($statut>0) touch(SITEROOT."CACHE/maj");
  unlock();

  return $id;
}

function move_files($id,$files_to_move,&$sets)

{
  foreach ($files_to_move as $file) {
    $src=SITEROOT.$file[filename];
    $dest=basename($file[filename]); // basename
    if (!$dest) die("ERROR: error in move_files");
    // new path to the file
    $dirdest="docannexe/$file[type]/$id";
    if (!file_exists(SITEROOT.$dirdest)) {
      if (!@mkdir(SITEROOT.$dirdest,0777 & octdec($GLOBALS[filemask]))) die("ERROR: impossible to create the directory \"$dir\"");
    }
    $dest=$dirdest."/".$dest;
    $sets[$file[name]]="'".addslashes($dest)."'";
    if ($src==SITEROOT.$dest) continue;
    rename($src,SITEROOT.$dest);
    chmod (SITEROOT.$dest,0666 & octdec($GLOBALS[filemask]));
    @rmdir(dirname($src)); // do not complain, the directory may not be empty
  }
}



function enregistre_personnes (&$context,$identite,$statut,$lock=TRUE)

{
  if ($lock) lock_write("objet","entites_personnes","personnes");
  // detruit les liens dans la table entites_personnes
 mysql_query("DELETE FROM $GLOBALS[tp]entites_personnes WHERE identite='$identite'") or die (mysql_error());

 if (!$context[nomfamille]) { if ($lock) unlock(); return; }

 if ($statut>-64 && $statut<-1) $statut=-1;
 if ($statut>1) $statut=1;

  $vars=array("prefix"=>1,"nomfamille"=>1,"prenom"=>1,"description"=>0,"fonction"=>0,"affiliation"=>0,"courriel"=>1);
  foreach (array_keys($context[nomfamille]) as $idtype) { // boucle sur les types
    foreach (array_keys($context[nomfamille][$idtype]) as $ind) { // boucle sur les ind
      // extrait les valeurs des differentes variables
      foreach ($vars as $var=>$strip) {
	$t=$strip ? strip_tags($context[$var][$idtype][$ind]) : $context[$var][$idtype][$ind];
	$bal[$var]=trim(addslashes(stripslashes($t)));
      }
      if (!$bal[prenom] && !$bal[nomfamille]) continue;
      // cherche si l'personne existe deja
      $result=mysql_query("SELECT id,statut FROM $GLOBALS[tp]personnes WHERE nomfamille='".$bal[nomfamille]."' AND prenom='".$bal[prenom]."'") or die (mysql_error());
      if (mysql_num_rows($result)>0) { // ok, l'personne existe deja
	list($id,$oldstatut)=mysql_fetch_array($result); // on recupere sont id et sont statut
	if (($statut>0 && $oldstatut<0) || ($oldstatut<=-64 && $statut>$oldstatut)) { // Faut-il publier l'personne ?
	  mysql_query("UPDATE $GLOBALS[tp]personnes SET statut='$statut' WHERE id='$id'") or die (mysql_error());
	}
      } else {
	$id=uniqueid("personnes");
	mysql_query ("INSERT INTO $GLOBALS[tp]personnes (id,statut,nomfamille,prenom) VALUES ('$id','$statut','$bal[nomfamille]','$bal[prenom]')") or die (mysql_error());
      }

      $ordre=$ind;

      // ajoute l'personne dans la table entites_personnes
      // ainsi que la description
      mysql_query("INSERT INTO $GLOBALS[tp]entites_personnes (idpersonne,identite,idtype,ordre,description,prefix,affiliation,fonction,courriel) VALUES ('$id','$identite','$idtype','$ordre','$bal[description]','$bal[prefix]','$bal[affiliation]','$bal[fonction]','$bal[courriel]')") or die (mysql_error());
    } // boucle sur les ind
  } // boucle sur les types
  if ($lock) unlock();
}



function enregistre_entrees (&$context,$identite,$statut,$lock=TRUE)

{
  if ($lock) lock_write("objets","entites_entrees","entrees","typeentrees");
  // detruit les liens dans la table entites_indexhs
  mysql_query("DELETE FROM $GLOBALS[tp]entites_entrees WHERE identite='$identite'") or die (mysql_error());

 if ($statut>-64 && $statut<-1) $statut=-1;
 if ($statut>1) $statut=1;

 // put the id's from entrees and autresentrees into idtypes
 $idtypes=$context[entrees] ? array_keys($context[entrees]) : array();
 if ($context[autresentrees]) $idtypes=array_unique(array_merge($idtypes,array_keys($context[autresentrees])));

  if (!$idtypes) { if ($lock) unlock(); return; }

  // boucle sur les differents entrees
  foreach ($idtypes as $idtype) {
    $entrees=$context[entrees][$idtype];
    if ($context[autresentrees][$idtype]) {
      if ($entrees) {
	$entrees=array_merge($entrees,preg_split("/,/",$context[autresentrees][$idtype]));
      } else {
	$entrees=preg_split("/,/",$context[autresentrees][$idtype]);
      }
    } elseif (!$entrees) continue;
    $result=mysql_query("SELECT nvimportable,utiliseabrev FROM $GLOBALS[tp]typeentrees WHERE statut>0 AND id='$idtype'") or die (mysql_error());
    if (mysql_num_rows($result)!=1) die ("erreur interne");
    $typeentree=mysql_fetch_assoc($result);

    foreach ($entrees as $entree) {
      // est-ce que $entree est un tableau ou directement l'entree ?
      if (is_array($entree)) {
	$lang=$entree[lang]=="--" ? "" : $entree[lang];
	$entree=$entree[nom];
      } else {
	$lang="";
      }
      // on nettoie le nom de l'entree
      $entree=trim(strip_tags($entree));
      myquote($entree);
      if (!$entree) continue; // etrange elle est vide... tant pis
      // cherche l'id de l'entree si elle existe
      $langcriteria=$lang ? "AND langue='$lang'" : "";
      $result=mysql_query("SELECT id,statut FROM $GLOBALS[tp]entrees WHERE (abrev='$entree' OR nom='$entree') AND idtype='$idtype' $langcriteria") or die(mysql_error());

      #echo $entree,":",mysql_num_rows($result),"<br>";
      if (mysql_num_rows($result)) { // l'entree exists
	list($id,$oldstatut)=mysql_fetch_array($result);

	$statutset="";
	if ($oldstatut<=-64 && $statut>$oldstatut) $statutset=$statut;
	if ($statut>0 && $oldstatut<0) $statutset="abs(statut)"; // faut-il publier ?
	if ($statutset) {
	  mysql_query("UPDATE $GLOBALS[tp]entrees SET statut=$statutset WHERE id='$id'") or die (mysql_error());	
	}
      } elseif ($typeentree[nvimportable]) { // l'entree n'existe pas. est-ce qu'on a le droit de l'ajouter ?
	// oui,il faut ajouter le mot cle
	$abrev=$typeentree[utiliseabrev] ? strtoupper($entree) : "";
	$id=uniqueid("entrees");
	mysql_query ("INSERT INTO $GLOBALS[tp]entrees (id,statut,nom,abrev,idtype,langue) VALUES ('$id','$statut','$entree','$abrev','$idtype','$lang')") or die (mysql_error());
      } else {
	$id=0;
	// on ne l'ajoute pas... pas le droit!
      }
      // ajoute l'entree dans la table entites_entrees
      // on pourrait optimiser un peu ca... en mettant plusieurs values dans 
      // une chaine et en faisant la requette a la fin !
      if ($id) {
	mysql_query("INSERT INTO $GLOBALS[tp]entites_entrees (identree,identite) VALUES ('$id','$identite')") or die (mysql_error());
      }
    } // boucle sur les entrees d'un type
  } // boucle sur les type d'entree
  if ($lock) unlock();
}


function lodel_strip_tags($text,$balises) 

{
  global $home;
  require_once($home."balises.php");
  static $accepted; // cache the accepted balise;
  global $multiplelevel,$xhtmlgroups;

  // simple case.
  if (!$balises) return strip_tags($text);

  if (!$accepted[$balises]) { // not cached ?
    $accepted[$balises]=array();

    // split the groupe of balises
    $groups=preg_split("/\s*;\s*/",$balises);
    array_push($groups,""); // balises speciales
    // feed the accepted string with accepted tags.
    foreach ($groups as $group) {
      // lodel groups
      if ($multiplelevel[$group]) {
	foreach($multiplelevel[$group] as $k=>$v) { $accepted[$balises]["r2r:$k"]=true; }
      }
	// xhtml groups
      if ($xhtmlgroups[$group]) {
	foreach($xhtmlgroups[$group] as $k=>$v) {
	  if (is_numeric($k)) { 
	    $accepted[$balises][$v]=true; // accept the tag with any attributs
	  } else {
	    // accept the tag with attributs matching unless it is already fully accepted
	    if (!$accepted[$balises][$k]) $accepted[$balises][$k][]=$v; // add a regexp
	  }
	}
      } // that was a xhtml group
    } // foreach group
  } // not cached.

#  print_r($accepted);

  $acceptedtags=$accepted[$balises];

  // the simpliest case.
  if (!$accepted) return strip_tags($text);

  $arr=preg_split("/(<\/?)(\w*:?\w+)\b([^>]*>)/",stripslashes($text),-1,PREG_SPLIT_DELIM_CAPTURE);

  $stack=array(); $count=count($arr);
  for($i=1; $i<$count; $i+=4) {
    #echo htmlentities($arr[$i].$arr[$i+1].$arr[$i+2]),"<br/>";
    if ($arr[$i]=="</") { // closing tag
      if (!array_pop($stack)) $arr[$i]=$arr[$i+1]=$arr[$i+2]="";
    } else { // opening tag
      $tag=$arr[$i+1];
      $keep=false;

#      echo $tag,"<br/>";
      if (isset($acceptedtags[$tag])) {
	// simple case.
	if ($acceptedtags[$tag]===true) { // simple
	  $keep=true;
	} else { // must valid the regexp
	  foreach ($acceptedtags[$tag] as $re) {
	    #echo $re," ",$arr[$i+2]," ",preg_match("/(^|\s)$re(\s|>|$)/",$arr[$i+2]),"<br/>";

	    if (preg_match("/(^|\s)$re(\s|>|$)/",$arr[$i+2])) { $keep=true; break; }
	  }
	}
#	echo "keep:$keep<br/>";
      }
      #echo ":",$arr[$i],$arr[$i+1],$arr[$i+2]," ",htmlentities(substr($arr[$i+2],-2)),"<br/>";
      if (substr($arr[$i+2],-2)!="/>")  // not an opening closing.
	array_push($stack,$keep); // whether to keep the closing tag or not.
      if (!$keep) { $arr[$i]=$arr[$i+1]=$arr[$i+2]=""; }

    }
  }

  // now, we know the accepted tags
  return join("",$arr);
}


function change_groupe_rec($id,$groupe)

{

##### a reecrire avec la table relation
##### a reecrire avec la table relation
##### a reecrire avec la table relation
##### a reecrire avec la table relation

  // cherche les publis a changer
  $ids=array($id);
  $idparents=array($id);

  do {
    $idlist=join(",",$idparents);
    // cherche les fils de idparents
    $result=mysql_query("SELECT id FROM $GLOBALS[tp]entites WHERE idparent IN ($idlist)") or die(mysql_error());

    $idparents=array();
    while ($row=mysql_fetch_assoc($result)) {
      array_push ($ids,$row[id]);
      array_push ($idparents,$row[id]);
    }
  } while ($idparents);

  // update toutes les publications
  $idlist=join(",",$ids);

  mysql_query("UPDATE $GLOBALS[tp]entites SET groupe='$groupe' WHERE id IN ($idlist)") or die(mysql_error());
  # cherche les ids
}


function loop_champs($context,$funcname)

{
  global $erreur;

  $result=mysql_query("SELECT * FROM $GLOBALS[tp]champs WHERE idgroupe='$context[id]' AND statut>0 AND edition!='' ORDER BY ordre") or die(mysql_error());

  $haveresult=mysql_num_rows($result)>0;
  if ($haveresult) call_user_func("code_before_$funcname",$context);

  while ($row=mysql_fetch_assoc($result)) {
    $localcontext=array_merge($context,$row);
    $localcontext[value]=$context[entite][$row[nom]];
    $localcontext[erreur]=$context[erreur][$row[nom]];

    call_user_func("code_do_$funcname",$localcontext);
  }

  if ($haveresult) call_user_func("code_after_$funcname",$context);
}

function loop_champs_require() { return array("id"); }



function loop_personnes(&$context,$funcname)

{
  global $id; // id de la publication

  $ind=0;
  $idtype=$context[id];
  $vars=array("prefix","nomfamille","prenom","description","fonction","affiliation","courriel");
  do {
    $vide=TRUE;
    $localcontext=$context;
    $localcontext[ind]=++$ind;
    foreach($vars as $v) {
      $localcontext[$v]=$context[$v][$idtype][$ind];
      if ($vide && $localcontext[$v]) $vide=FALSE;
    }
    if ($vide && !$GLOBALS[plus][$idtype]) break;
    call_user_func("code_do_$funcname",$localcontext);
    if ($vide) break;
  } while (1);
}

function loop_personnes_require() { return array("id"); }


function extrait_personnes($identite,&$context)

{
  $result=mysql_query("SELECT * FROM $GLOBALS[tp]personnes,$GLOBALS[tp]entites_personnes WHERE idpersonne=id  AND identite='$identite'") or die(mysql_error());

  $vars=array("prefix","nomfamille","prenom","description","fonction","affiliation","courriel");
  while($row=mysql_fetch_assoc($result)) {
    foreach($vars as $var) {
      $context[$var][$row[idtype]][$row[ordre]]=$row[$var];
    }
  }
}

function extrait_entrees($identite,&$context)

{
  $result=mysql_query("SELECT * FROM $GLOBALS[tp]entrees,$GLOBALS[tp]entites_entrees WHERE identree=id  AND identite='$identite'") or die(mysql_error());

  while($row=mysql_fetch_assoc($result)) {
    if ($context[entrees][$row[idtype]]) {
      array_push($context[entrees][$row[idtype]],$row[nom]);
    } else {
      $context[entrees][$row[idtype]]=array($row[nom]);
    }
  }
}


// makeselect


function makeselectentrees (&$context)
     // le context doit contenir les informations sur le type a traiter
{
  $entreestrouvees=array();
  $entrees=$context[entrees][$context[id]];
#  echo "type:",$context[id];print_r($context[entrees]);
  makeselectentrees_rec(0,"",$entrees,$context,$entreestrouvees);
  $context[autresentrees]=$entrees ? join(", ",array_diff($entrees,$entreestrouvees)) : "";
}

function makeselectentrees_rec($idparent,$rep,$entrees,&$context,&$entreestrouvees)

{
  if (!$context[tri]) die ("ERROR: internal error in makeselectentrees_rec");
  $result=mysql_query("SELECT id, abrev, nom FROM $GLOBALS[tp]entrees WHERE idparent='$idparent' AND idtype='$context[id]' ORDER BY $context[tri]") or die (mysql_error());

  while ($row=mysql_fetch_assoc($result)) {
    $selected=$entrees && (in_array($row[abrev],$entrees) || in_array($row[nom],$entrees)) ? " selected" : "";
   if ($selected) array_push($entreestrouvees,$row[nom],$row[abrev]);
   $value=$context[utiliseabrev] ? $row[abrev] : $row[nom];
    echo "<option value=\"$value\"$selected>$rep$row[nom]</option>\n";
    makeselectentrees_rec($row[id],$rep.$row[nom]."/",$entrees,$context,$entreestrouvees);
  }
}


function makeselectgroupes() 

{
  global $context;
      
  $result=mysql_query("SELECT id,nom FROM $GLOBALS[tp]groupes") or die (mysql_error());

  while ($row=mysql_fetch_assoc($result)) {
    $selected=$context[groupe]==$row[id] ? " SELECTED" : "";
    echo "<OPTION VALUE=\"$row[id]\"$selected>$row[nom]</OPTION>\n";
  }
}

function makeselecttype($classe)

{
  global $context;

  if ($context[typedocfixe]) $critere="AND type='$context[typedoc]'";

  $result=mysql_query("SELECT id,type,titre FROM $GLOBALS[tp]types WHERE statut>0 AND classe='$classe' $critere AND type NOT LIKE 'documentannexe-%'") or die (mysql_error());
  while ($row=mysql_fetch_assoc($result)) {
    $selected=$context[idtype]==$row[id] ? " selected" : "";
    $nom=$row[titre] ? $row[titre] : $row[type];
    echo "<option value=\"$row[id]\"$selected>$nom</option>\n";
  }
}


function makeselectdate() {
  global $context;

  foreach (array("maintenant",
		 "jours",
		 "mois",
		 "ann�es") as $date) {
    $selected=$context[dateselect]==$date ? "selected" : "";
    echo "<option value=\"$date\"$selected>$date</option>\n";
  }
}


function loop_mltext($context,$funcname) {

#  print_r($context[value]);
  if (is_array($context[value])) {
    foreach($context[value] as $lang=>$value) {
      $localcontext=$context;
      $localcontext[lang]=$lang;
      $localcontext[value]=$value;
      call_user_func("code_do_$funcname",$localcontext);
    }
  # pas super cette regexp... mais l'argument a deja ete processe !
  } elseif (preg_match_all("/&lt;r2r:ml lang\s*=&quot;(\w+)&quot;&gt;(.*?)&lt;\/r2r:ml&gt;/s",$context[value],$results,PREG_SET_ORDER) ||
	    preg_match_all("/<r2r:ml lang\s*=\"(\w+)\">(.*?)<\/r2r:ml>/s",$context[value],$results,PREG_SET_ORDER)    ) {

    foreach($results as $result) {
      $localcontext=$context;
      $localcontext[lang]=$result[1];
      $localcontext[value]=$result[2];
      call_user_func("code_do_$funcname",$localcontext);
    }
  }

  $lang=$context[addlanginmltext][$context[nom]];
#  echo "lang=$lang  $context[nom]";
#  print_r($context[addlanginmltext]);
  if ($lang) {
    $localcontext=$context;
    $localcontext[lang]=$lang;
    $localcontext[value]="";
    call_user_func("code_do_$funcname",$localcontext);
  }
}


?>