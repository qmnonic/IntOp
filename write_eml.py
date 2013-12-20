from lxml import etree
from collections import OrderedDict as OD



## Writes an set of elements for "responsible party (http://knb.ecoinformatics.org/software/eml/eml-2.1.1/eml-party.html)"
## which can then be appended into the root xml document.  
## root_name is the base of the node you want to create. 
## node_params is a dictionary of element:value pairs
## If an element in a dictionary is a key:string, it is assumed
## that key is the xml element, and string is the value.  Otherwise it should be a list of the form: key:[value,[attribute,value],[attribute:value]...etc]
## to encode attributes in xml elements.  

def write_eml(node_params, root_name, parent_node = None):
	if parent_node is None:
		#root_name = node_params.keys()[0]
		root = etree.Element(root_name)
	else:
		root = parent_node
	
	for k,v in node_params.items():

		## The case where there is no attribute
		if isinstance(v,str):
			tmp_el = etree.Element(k)
			tmp_el.text = v
			root.append(tmp_el)

		## The case where we have attributes

		if isinstance(v,list):
			tmp_el = etree.Element(k)
			tmp_el.text = v[0]
			for i in range(len(v))[1:]:
				tmp_el.set(v[i][0],v[i][1])
			root.append(tmp_el)

		if isinstance(v,dict):
			sub_node = write_eml(node_params = v, root_name = k)
			root.append(sub_node)



	return root




### Test simple case
test_d = {"ted":"hart","xml":"md"}
### Test case with parameters
test = {"eml":{"ted":"hart","zinger":["haha",["id","123o2"],["funny","yes"]]}}



testeml = OD(dataset = OD(individualName = OD(givenName="John",surName="Friel"),
	title = ["CUMV Amphibian Collection",["lang","eng"]],
	organizationName = "Cornell University Museum of Vertebrates",
	positionName = "Curator"

	))


### Real EML test:  Tries to recreate part of http://ipt.vertnet.org:8080/ipt/eml.do?r=cumv_amph&v=3



root = write_eml(node_params = testeml,root_name = "eml")

print(etree.tostring(root,  encoding="UTF-8",pretty_print=True,xml_declaration=True))



with open('eml_test.xml', 'w') as f:
	f.write(etree.tostring(root, encoding="UTF-8",pretty_print=True,xml_declaration=True))




