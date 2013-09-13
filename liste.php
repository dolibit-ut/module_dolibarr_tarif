<?php
	require('config.php');
	require('class/tarif.class.php');
	require_once DOL_DOCUMENT_ROOT.'/core/lib/product.lib.php';
	require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
	require_once DOL_DOCUMENT_ROOT.'/product/class/html.formproduct.class.php';
	
	llxHeader('','Liste des tarifs par conditionnement','','');
	
	if(is_file(DOL_DOCUMENT_ROOT."/lib/product.lib.php")) require_once(DOL_DOCUMENT_ROOT."/lib/product.lib.php");
	else require_once(DOL_DOCUMENT_ROOT."/core/lib/product.lib.php");
	
	require_once(DOL_DOCUMENT_ROOT."/product/class/product.class.php");
	
	$ATMdb = new Tdb;	
	$product = new Product($db);
	$result=$product->fetch($_REQUEST['fk_product']);	
		
	$head=product_prepare_head($product, $user);
	$titre=$langs->trans("CardProduct".$product->type);
	$picto=($product->type==1?'service':'product');
	dol_fiche_head($head, 'tabTarif1', $titre, 0, $picto);
	
	$object = $product;
	$form = new Form($db);
	$formproduct = new FormProduct($db);
	
	print '<table class="border" width="100%">';
	
	// Ref
	print '<tr>';
	print '<td width="15%">'.$langs->trans("Ref").'</td><td colspan="2">';
	print $form->showrefnav($object,'fk_product','',1,'fk_product');
	print '</td>';
	print '</tr>';
	
	// Label
	print '<tr><td>'.$langs->trans("Label").'</td><td>'.$object->libelle.'</td>';
	print '</tr>';
	// TVA
    print '<tr><td>'.$langs->trans("VATRate").'</td><td>'.vatrate($object->tva_tx.($object->tva_npr?'*':''),true).'</td></tr>';

    // Price
	print '<tr><td>'.$langs->trans("SellingPrice").'</td><td>';
	if ($object->price_base_type == 'TTC')
	{
		print price($object->price_ttc).' '.$langs->trans($object->price_base_type);
	}
	else
	{
		print price($object->price).' '.$langs->trans($object->price_base_type);
	}
	print '</td></tr>';
	
	// Price minimum
	print '<tr><td>'.$langs->trans("MinPrice").'</td><td>';
	if ($object->price_base_type == 'TTC')
	{
		print price($object->price_min_ttc).' '.$langs->trans($object->price_base_type);
	}
	else
	{
		print price($object->price_min).' '.$langs->trans($object->price_base_type);
	}
	print '</td></tr>';
	
	// Status (to sell)
	print '<tr><td>'.$langs->trans("Status").' ('.$langs->trans("Sell").')</td><td>';
	print $object->getLibStatut(2,0);
	print '</td></tr>';
	
	print "</table>\n";
	
	print "</div>\n";
	
	print '<div class="tabsAction">
				<a class="butAction" href="?action=add&fk_product='.$object->id.'">Ajouter un conditionnement</a>
			</div><br>';
	
	/***********************************
	 * Traitements actions
	 ***********************************/
	if(isset($_REQUEST['action']) && !empty($_REQUEST['action']) && $_REQUEST['action'] == 'add'){
		
		print '<table class="notopnoleftnoright" width="100%" border="0" style="margin-bottom: 2px;" summary="">';
		print '<tbody><tr>';
		print '<td class="nobordernopadding" valign="middle"><div class="titre">Nouveau conditionnement</div></td>';
		print '</tr></tbody>';
		print '</table>';
		
		print '<form action="" method="POST">';
		print '<input type="hidden" name="action" value="add_conditionnement">';
		print '<input type="hidden" name="id" value="'.$object->id.'">';
		print '<table class="border" width="100%">';

		// VAT
        print '<tr><td width="20%">'.$langs->trans("VATRate").'</td><td>';
        print $form->load_tva("tva_tx",$object->tva_tx,$mysoc,'',$object->id,$object->tva_npr);
        print '</td></tr>';

		// Price base
		print '<tr><td width="20%">Base du prix</td>';
		print '<td>';
		//print $form->select_PriceBaseType($object->price_base_type, "price_base_type");
		print 'HT</td>';
		print '</tr>';

		// Price
		print '<tr><td width="20%">';
		print 'Prix de vente';
		print '</td><td><input type="hidden" name="prix" value="'.number_format($object->price,2,",","").'"><input size="10" name="prix_visu" value="'.number_format($object->price,2,",","").'" readonly></td></tr>';
		
		// Remise
		print '<tr><td width="20%">';
		print 'Pourcentage de remise';
		print '</td><td><input value="" size="10" name="remise"></td></tr>';
		
		//Quantité
		print '<tr><td width="20%">';
		print 'Quantit&eacute;';
		print '</td><td><input value="" size="10" name="quantite"></td></tr>';
		
		print '<tr><td width="20%">';
		print 'Unit&eacute;';
		print '</td><td>';
		print $formproduct->select_measuring_units("weight_units", "weight", $object->weight_units);
		print '</td></tr>';

		print '</table>';

		print '<center><br><input type="submit" class="button" value="'.$langs->trans("Save").'" name="save">&nbsp;';
		print '<input type="submit" class="button" value="Annuler" name="back"></center>';

		print '<br></form>';
	}
	elseif(isset($_REQUEST['action']) && !empty($_REQUEST['action']) && $_REQUEST['action'] == 'add_conditionnement' && isset($_REQUEST['save'])) {
		switch($_POST['weight_units']){
			case -9:
				$unite = "μg";
				break;
			case -6:
				$unite = "mg";
				break;
			case -3:
				$unite = "g";
				break;
			case 0:
				$unite = "kg";
				break;
			case 3:
				$unite = "tonnes";
				break;
		}	
			
		$Ttarif = new TTarif;
		$Ttarif->tva_tx = $_POST['tva_tx'];
		$Ttarif->price_base_type = 'HT';
		$Ttarif->fk_user_author = $user->id;
		$Ttarif->prix = number_format(str_replace(",", ".", $_POST['prix']),2,".","");
		$Ttarif->quantite =  number_format(str_replace(",", ".", $_POST['quantite']),2,".","");
		$Ttarif->unite = $unite;
		(isset($_POST['remise']) && !empty($_POST['remise'])) ? $Ttarif->remise_percent = $_POST['remise'] : "" ;
		$Ttarif->unite_value = $_POST['weight_units'];
		$Ttarif->fk_product = $_POST['id'];
		$Ttarif->save($ATMdb);
	}
	elseif(isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id']))
	{
		$Ttarif = new TTarif;
		$Ttarif->load($ATMdb,$_GET['id']);
		$Ttarif->delete($ATMdb);
	}

	
	/**********************************
	 * Liste des tarifs
	 **********************************/
	$TConditionnement = array();
	
	$sql = "SELECT tc.rowid AS 'id', tc.tva_tx AS tva, tc.price_base_type AS base, tc.quantite as quantite, tc.unite AS unite, tc.remise_percent AS remise, tc.prix AS prix, p.weight_units AS base_poids, tc.unite_value AS unite_value,((tc.quantite * POWER(10,(tc.unite_value-p.weight_units))) * tc.prix) - ((tc.quantite * POWER(10,(tc.unite_value-p.weight_units))) * tc.prix) * (tc.remise_percent/100)  AS 'Total','' AS 'Supprimer'
			FROM ".MAIN_DB_PREFIX."tarif_conditionnement AS tc
				LEFT JOIN ".MAIN_DB_PREFIX."product AS p ON (tc.fk_product = p.rowid)
			WHERE fk_product = ".$product->id."
			ORDER BY unite_value, quantite ASC";
	
	$r = new TSSRenderControler(new TTarif);
		
	print $r->liste($ATMdb, $sql, array(
		'limit'=>array('nbLine'=>1000)
		,'title'=>array(
			'tva'=>'Taux TVA'
			,'base' => 'Type du Prix'
			,'quantite'=>'Quantité'
			,'unite'=>'Unit&eacute;'
			,'prix'=>'Tarif (€)'
			,'remise' => 'Remise (%)'
			,'Total' => 'Total (€)'
			,'Supprimer' => 'Supprimer'
		)
		,'type'=>array('date_debut'=>'date','date_fin'=>'date','tva' => 'number', 'prix'=>'money', 'Total' => 'money' , 'quantite' => 'number')
		,'hide'=>array(
			'id'
			,'base_poids'
			,'unite_value'
			,'tva'
			,'base'
		)
		,'link'=>array(
			'Supprimer'=>'<a href="?id=@id@&action=delete&fk_product='.$object->id.'" onclick="return confirm(\'Êtes-vous sûr de vouloir supprimer ce conditionnement?\');"><img src="img/delete.png"></a>'
		)
	));
	?>
	<br>
	
	