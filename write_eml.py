from lxml import etree
from collections import OrderedDict as OD


## This function will write a full XML document based on a dictionary or ordered dictionary.
## Ideally this will allow an end user to parse a text input document into the expected dictionary
## format and then output xml.
## Parameters:
## root_name is the base of the node you want to create. 
## node_params is a dictionary of element:value pairs
## If an element in a dictionary is a key:string, it is assumed that key is the xml element, and string is the value.  
## If an element in a dictionary is a key:list, it is assumed key:[value,[attribute,value],[attribute:value]...etc], and attributes will be added as presented in the list
## If an element in a dictionary is a key:dictionary, then the dictionary will take key to be the sub root element, and all elements in the dictionary will be nested withn the key element


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



### Preparesd example from http://ipt.vertnet.org:8080/ipt/eml.do?r=cumv_amph&v=3


testeml = OD(dataset = OD(individualName = OD(givenName="John",surName="Friel"),
	title = ["CUMV Amphibian Collection",["lang","eng"]],
	organizationName = "Cornell University Museum of Vertebrates",
	positionName = "Curator",
	address = OD( 
		deliveryPoint = "159 Sapsucker Woods Road",
		city = "Ithaca",
		administrativeArea = "NY",
		postalCode = "14850-1923",
		country = "US"

		)
	)
	)



root = write_eml(node_params = testeml,root_name = "eml")

### Uncomment lines to either write to file or just print to screen


#print(etree.tostring(root,  encoding="UTF-8",pretty_print=True,xml_declaration=True))



with open('eml_test.xml', 'w') as f:
	f.write(etree.tostring(root, encoding="UTF-8",pretty_print=True,xml_declaration=True))




