/*
*   Author: StanleyTso.com
*	GreenGene project
*
*/
var gene = new Array(6);//for labels
gene[0] = new Array('AA', 'Aa', 'aA', 'aa');
gene[1] = new Array('AA', 'Aa', 'aA / aa');
gene[2] = new Array('AA', 'Aa / aA / aa');
gene[3] = new Array('AA / Aa', 'aA', 'aa');
gene[4] = new Array('AA / Aa / aA', 'aa');
gene[5] = new Array('AA', 'Aa / aA', 'aa');
var label = new Array(5);//for text field that shows the ratio
label[0] = new Array(9,3,3,1);
label[1] = new Array(9,3,4);
label[2] = new Array(9,7);
label[3] = new Array(12,3,1);
label[4] = new Array(15,1);

function setTx(index){//fill up epistasis ratio in the textfield
	if (document.problem)
	for(var i = 0; i < label[index].length; i++)
		if (eval('document.problem.T'+i))
			eval('document.problem.T'+i+'.value='+label[index][i]);
}

function writit(text,id)//write text into DIV tag with id given
{
   var x;
   if (document.getElementById)
   {
       x = document.getElementById(id);
	   if (x){
	       x.innerHTML = '';
	       x.innerHTML = text;
	   }
   }
   else if (document.all)
   {
       x = document.all[id];
	   if (x)
       	x.innerHTML = text;
   }
   else if (document.layers)
   {
      x = document.layers[id];
	  if (x){
	      x.document.open();
	      x.document.write(text);
	      x.document.close();
	  }
   }
}

function setTip(col, index){//setup all tips and labels for the 3 genes
	if (index>=0){
		if (col==2&&index>=0&&index<5){
			setTx(index);
		}
		for (var i = 0; i < 4; i++){
			if (gene[index].length>i){
				show('pheno'+col+i);
				show('tip'+col+i);
				if (col==2){
					if (document.problem)
						if (document.problem.epistCheck.checked){
								show('T'+i);
								show('C'+i);
							}else{
								hide('T'+i);
								hide('C'+i);
							}
				}
				if (gene[index][i])
					writit(gene[index][i],'tip'+col+i);
			}else{
				hide('pheno'+col+i);
				hide('tip'+col+i);
				if (col==2){
					hide('C'+i);
					hide('T'+i);
				}
			}
		}
	}
}

function reorder(moveup){//order box
	var form = document.problem;
	var ch='x';
	var tempSet = new Array(2);//value text pair
	if (moveup){
		if (form.order.selectedIndex > 0){
			ch = form.order.selectedIndex - 1;
			tempSet[0] = form.order.options[ch].text;
			tempSet[1] = form.order.options[ch].value;
			form.order.options[ch] = new Option(form.order.options[ch+1].text, form.order.options[ch+1].value);
			form.order.options[ch+1] = new Option(tempSet[0], tempSet[1]);
		}
	}else{
		if (form.order.selectedIndex < 2 && form.order.selectedIndex >= 0){
			ch = form.order.selectedIndex + 1;
			tempSet[0] = form.order.options[ch].text;
			tempSet[1] = form.order.options[ch].value;
			form.order.options[ch] = new Option(form.order.options[ch-1].text, form.order.options[ch-1].value);
			form.order.options[ch-1] = new Option(tempSet[0], tempSet[1]);
		}
	}
	if (ch!='x'){
		form.order.selectedIndex = ch;
	}
	form.ordering.value = form.order.options[0].value+''+form.order.options[1].value+''+form.order.options[2].value;
}

function clearOptions(sel)//clean select options
{
	if (sel.selectedIndex!=undefined){
		while (sel.options[0])sel.options[0]=null;
		sel.options[0] = new Option('--------------');
	}
}
function trait(col)//populate pheno options with selected trait
{
	var phenSelect = new Array(4);
	var i;
	for (i = 0;i<4;i++)
	{
		phenSelect[i] = eval('(document.problem.pheno'+col+''+i+') ? document.problem.pheno'+col+''+i+': false;');
		clearOptions(phenSelect[i]);
	}
	if (eval('document.problem.trait'+col+'.selectedIndex > 0')){
		i=eval('document.problem.trait'+col+'.selectedIndex') - 1;//first element is for display
		for (var j=0;j<tdb[i][1].length;j++)
		{
			phenSelect[0].options[j] = new Option(tdb[i][1][j][1], tdb[i][1][j][1]);
			phenSelect[1].options[j] = new Option(tdb[i][1][j][1], tdb[i][1][j][1]);
			phenSelect[2].options[j] = new Option(tdb[i][1][j][1], tdb[i][1][j][1]);
			if (phenSelect[3].selectedIndex!=undefined)
				phenSelect[3].options[j] = new Option(tdb[i][1][j][1], tdb[i][1][j][1]);
		}
	}
	i = 0;
	while(phenSelect[i]&&phenSelect[i].selectedIndex!=undefined){
		if (phenSelect[i].options[0]!=null)
			phenSelect[i].selectedIndex = 0;
		i++;
	}
}

function epi(form, sel){
	var epistList = sel.selectedIndex - 1;
	var phenSelect = new Array(4);
	for (var i = 0;i<4;i++){
		phenSelect[i] = eval('document.problem.pheno2'+i);
		if (phenSelect[i].selectedIndex!=undefined)
			clearOptions(phenSelect[i]);
	}
	for (var i=0;i<4;i++)
		eval('document.problem.T'+i+'.value = \'\';');
	if (epistList >= 0)
		setTip('2', epistList);
	trait(2);//create new dropdown box for the new selected Trait
}

function hide(id){
	if (document.getElementById(id))document.getElementById(id).style.visibility = 'hidden';
}
function show(id){
	if (document.getElementById(id))document.getElementById(id).style.visibility = 'visible';
}
function is_hidden(id){
	if (document.getElementById(id)) return document.getElementById(id).style.visibility == 'hidden';
}
//form check section:
function checkPhenos(){
	var form = 'document.problem';
	var ck;
	for (var i = 0; i < 3; i++){
		if (eval(form+'.trait'+i+'.selectedIndex!=undefined;')){
			if (eval(form+'.trait'+i+'.selectedIndex') > 0){
				ck = new Array(tdb[eval(form+'.trait'+i+'.selectedIndex')-1][1].length);
				for (var j = 0; j < ck.length; j++)
					ck[j] = false;
				for (var k = 0; k < 4; k++){
					if (eval('document.problem.pheno'+i+''+k) && !is_hidden('pheno'+i+''+k)){
						if (ck[eval('document.problem.pheno'+i+''+k+'.selectedIndex')]){
							return false;
						}else{
							ck[eval('document.problem.pheno'+i+''+k+'.selectedIndex')] = true;
						}
					}
				}
			}
		}else{
			var wch = new Array();
			var k = 0;
			var endLoop = 0;
			var checkDom = true;
			if (i == 2){
				if (document.problem.epistCheck.checked){
					while(eval('document.problem.T'+k)&&eval('document.problem.T'+k+'.value!=\'\''))
						k++;
					endLoop = k;
				}else{
					endLoop = (eval('document.problem.dom'+i+'.checked'))?2:3;
				}
			}else{
				endLoop = (eval('document.problem.dom'+i+'.checked'))?2:3;
			}
			for(var j = 0; j < endLoop;j++){
				if (eval('document.problem.pheno'+i+''+j)){
					for (var k = 0;k<wch.length;k++)
						if (wch[k]==eval('document.problem.pheno'+i+''+j+'.value'))
							return false;
					wch[j] = eval('document.problem.pheno'+i+''+j+'.value');
				}
			}
		}
	}
	return true;
}

function checkTraits(){
	var sel = 'document.problem.trait';
	if (eval(sel+'0.selectedIndex!=undefined;'))
	return !eval(sel+'0.options['+sel+'0.selectedIndex].value == '+sel+'1.options['+sel+'1.selectedIndex].value || '+sel+'2.options['+sel+'2.selectedIndex].value == '+sel+'1.options['+sel+'1.selectedIndex].value || '+sel+'0.options['+sel+'0.selectedIndex].value == '+sel+'2.options['+sel+'2.selectedIndex].value || '+sel+'0.selectedIndex == 0 || '+sel+'1.selectedIndex == 0 || '+sel+'2.selectedIndex == 0');
	return eval(sel+'0.value') != '' && eval(sel+'1.value') != '' && eval(sel+'2.value') != '';
}

function EP0(checked){
	if (checked){
		hide('DO1');
		hide('DO0');
		hide('LK1');
		hide('LK0');
		show('RA1');
		show('RA0');
		epi(document.problem, document.problem.epist);
	}else{
		hide('T0');
		hide('T1');
		hide('T2');
		hide('T3');
		hide('C0');
		hide('C1');
		hide('C2');
		hide('C3');
		hide('RA1');
		hide('RA0');
		show('DO1');
		show('DO0');
		show('LK1');
		show('LK0');
		DOM(2, document.problem.dom2.checked);
	}
}

function loadData(){
	for (var i = 0; i < 2; i++)
		eval('DOM('+i+',document.problem.dom'+i+'.checked);')
	EP0(document.problem.epistCheck.checked);
}

function DOM(id, checked){
	setTip(id, (checked)?4:5);
}

function problemCheck(form){
	var error = '';
	if (form.problemname.value=='')
		error += '  *Problem Name required.\n';
	if (form.progpermating.value=='')
		error += '  *Progeny Per Mating value required.\n';
	else
		if (form.progpermating.value*1!=form.progpermating.value){
			error += '  *Progeny Per Mating value must be numerical.\n';
		}else if (form.progpermating.value*1<0){
			error += '  *Progeny Per Mating value must be larger than and equal to 0.\n';
		}
	if (form.totalprogeny.value=='')
		error += '  *Total Progeny value required.\n';
	else
		if (''+(form.totalprogeny.value*1)!=form.totalprogeny.value){
			error += '  *Total Progeny value must be numerical.\n';
		}else if (form.progpermating.value*1<0){
			error += '  *Total Progeny value must be larger than and equal to 0.\n';
		}
	if (form.totalprogeny.value*1 < form.progpermating.value*1)
		error += '  *Max Progeny must be larger than Progeny Per Mating.\n';
	if (!checkTraits())
		error += '  *All 3 Genes must be different and cannot be empty.\n';
	if (!checkPhenos())
		error += '  *Each Gene must have different Phenotype selected.\n';
	if ((form.check01.checked&&form.linkdist01.value=='')||(form.check12.checked&&form.linkdist12.value==''))
		error += '  *Linkage Distance value required.\n';
	else
		if ((form.check01.checked&&form.linkdist01.value*1!=form.linkdist01.value) || (form.check12.checked&&form.linkdist12.value*1!=form.linkdist12.value))
			error += '  *Linkage Distance value must be numerical.\n';
		else
			if ((form.check01.checked&&form.linkdist01.value<0) || (form.check01.checked&&form.linkdist01.value>50)
			 || (form.check12.checked&&form.linkdist12.value<0) || (form.check12.checked&&form.linkdist12.value>50))
				error += '  *Linkage Distance must be greater than or equal to 0, but less than 50.\n';
	if (form.epist.selectedIndex==0&&!is_hidden('RA1'))
		error += '  *Please choose an Epistatic ratio.\n';
	if (error!='')
		alert('Please fix the following problem(s):\n\n'+error);
	else
		form.submit();
}
