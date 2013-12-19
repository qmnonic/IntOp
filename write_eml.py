from lxml import etree

## Writes an set of elements for "responsible party (http://knb.ecoinformatics.org/software/eml/eml-2.1.1/eml-party.html)"
## which can then be appended into the root xml document.  
## root_name is the base of the node you want to create. 
## node_params is a dictionary of element:value pairs
## If an element in a dictionary is a key:string, it is assumed
## that key is the xml element, and string is the value.  Otherwise it should be a list of the form: key:[value,[attribute,value],[attribute:value]...etc]
## to encode attributes in xml elements.  

def eml_subnode(root_name,node_params):
	tmp_root = etree.Element(root_name)
	
	for k,v in node_params.iteritems():

		## The case where there is no attribute
		if isinstance(v,str):
			tmp_el = etree.Element(k)
			tmp_el.text = v
			tmp_root.append(tmp_el)

		## The case where we have attributes

		if isinstance(v,list):
			tmp_el = etree.Element(k)
			tmp_el.text = v[0]
			for i in range(len(v))[1:]:
				print v[i][1]
				tmp_el.set(v[i][0],v[i][1])
			tmp_root.append(tmp_el)



	return tmp_root

### Test simple case
test_d = {"ted":"hart","xml":"md"}
### Test case with parameters
test_l = {"ted":"hart","zinger":["haha",["id","123o2"],["funny","yes"]]}

root = eml_subnode("test",test_l)

print(etree.tostring(root, pretty_print=True))



#with open('/Users/tedhart/Documents/output.xml', 'w') as f:
#	f.write(etree.tostring(my_doc, encoding="UTF-8",pretty_print=True,xml_declaration=True))




