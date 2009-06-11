<?php
include("../include.php");

//die(htmlentities("'Nuñez", ISO8859-1));

$where = (!isset($_GET["id"])) ? "" : "AND i.id IN (SELECT i2t.instanceID FROM contacts_instances_to_tags i2t WHERE i2t.tagID = " . implode(" OR i2t.tagID = ", explode("|", $_GET["id"])) . ")";

$result = db_query("SELECT
				o.id,
				'" . url_base() . "/contacts/contact.php?id=' + cast(o.id as varchar) link,
				(SELECT t1.tag FROM contacts_tags t1 JOIN contacts_instances_to_tags i2t1 ON t1.id = i2t1.tagID WHERE t1.is_active = 1 AND t1.type_id = 10 AND i2t1.instanceID = o.instanceCurrentID) salutation,
				i.varchar_01 firstname,
				i.varchar_02 lastname,
				(SELECT t2.tag FROM contacts_tags t2 JOIN contacts_instances_to_tags i2t2 ON t2.id = i2t2.tagID WHERE t2.is_active = 1 AND t2.type_id = 11 AND i2t2.instanceID = o.instanceCurrentID) suffix,
				i.varchar_04 org,
				i.varchar_05 title,
				i.varchar_06 address1,
				i.varchar_07 address2,
				z.city,
				z.state,
				RIGHT('00000' + RTRIM(i.numeric_01), 5) zip,
				i.varchar_08 phone,
				i.varchar_09 fax,
				i.varchar_10 cell,
				i.varchar_11 email,
				(SELECT count(*) FROM contacts_instances_to_tags i2t JOIN contacts_tags t ON i2t.tagID = t.id JOIN contacts_tags_types tt ON t.type_id = tt.id WHERE tt.is_active = 1 AND t.is_active = 1 AND i2t.instanceID = i.id) tagcount,
				(SELECT count(*) FROM contacts_instances_to_tags i2t WHERE i2t.instanceID = i.id AND i2t.tagID = 216) tagAsset,
				(SELECT count(*) FROM contacts_instances_to_tags i2t WHERE i2t.instanceID = i.id AND i2t.tagID = 217) tagComFin,
				(SELECT count(*) FROM contacts_instances_to_tags i2t WHERE i2t.instanceID = i.id AND i2t.tagID = 7)   tagEconDev,
				(SELECT count(*) FROM contacts_instances_to_tags i2t WHERE i2t.instanceID = i.id AND i2t.tagID = 220) tagFunders,
				(SELECT count(*) FROM contacts_instances_to_tags i2t WHERE i2t.instanceID = i.id AND i2t.tagID = 218) tagTech,
				(SELECT count(*) FROM contacts_instances_to_tags i2t WHERE i2t.instanceID = i.id AND i2t.tagID = 27)  tagWorkforce,
				(SELECT count(*) FROM contacts_instances_to_tags i2t WHERE i2t.instanceID = i.id AND i2t.tagID = 13)  tagHigherEd,
				(SELECT count(*) FROM contacts_instances_to_tags i2t WHERE i2t.instanceID = i.id AND i2t.tagID = 78)  tagForProfit,
				(SELECT count(*) FROM contacts_instances_to_tags i2t WHERE i2t.instanceID = i.id AND i2t.tagID = 31)  tagGovt,
				(SELECT count(*) FROM contacts_instances_to_tags i2t WHERE i2t.instanceID = i.id AND i2t.tagID = 93)  tagMedia,
				(SELECT count(*) FROM contacts_instances_to_tags i2t WHERE i2t.instanceID = i.id AND i2t.tagID = 24)  tagNonProfit,
				(SELECT count(*) FROM contacts_instances_to_tags i2t WHERE i2t.instanceID = i.id AND i2t.tagID = 80)  tagVendor,
				(SELECT count(*) FROM contacts_instances_to_tags i2t WHERE i2t.instanceID = i.id AND i2t.tagID = 131) tagSeedcoBoard,
				(SELECT count(*) FROM contacts_instances_to_tags i2t WHERE i2t.instanceID = i.id AND i2t.tagID = 219) tagSFSBoard,
				(SELECT count(*) FROM contacts_instances_to_tags i2t WHERE i2t.instanceID = i.id AND i2t.tagID = 221) tagImported
			FROM contacts o
			INNER JOIN contacts_instances i ON i.id = o.instanceCurrentID
			LEFT  JOIN zip_codes z ON i.numeric_01 = z.zip
			WHERE o.type_id = 22 AND o.is_active = 1 {$where}
			ORDER BY 
				lastname, 
				firstname");
							

$return = '<?xml version="1.0"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">
 <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
  <Author>joshuar</Author>
  <LastAuthor>joshuar</LastAuthor>
  <Created>2006-06-09T17:38:52Z</Created>
  <LastSaved>2006-06-09T18:33:53Z</LastSaved>
  <Company>seedco</Company>
  <Version>11.6568</Version>
 </DocumentProperties>
 <ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel">
  <WindowHeight>13035</WindowHeight>
  <WindowWidth>15195</WindowWidth>
  <WindowTopX>480</WindowTopX>
  <WindowTopY>60</WindowTopY>
  <ProtectStructure>False</ProtectStructure>
  <ProtectWindows>False</ProtectWindows>
 </ExcelWorkbook>
 <Styles>
  <Style ss:ID="Default" ss:Name="Normal">
   <Alignment ss:Vertical="Bottom"/>
   <Borders/>
   <Font/>
   <Interior/>
   <NumberFormat/>
   <Protection/>
  </Style>
  <Style ss:ID="s23">
   <NumberFormat ss:Format="00000"/>
  </Style>
  <Style ss:ID="s24">
   <Font x:Family="Swiss" ss:Bold="1"/>
   <Interior ss:Color="#FFFFCC" ss:Pattern="Solid"/>
  </Style>
  <Style ss:ID="s25">
   <Font x:Family="Swiss" ss:Size="8" ss:Bold="1"/>
   <Interior ss:Color="#FFFFCC" ss:Pattern="Solid"/>
  </Style>
  <Style ss:ID="s26">
   <Font x:Family="Swiss" ss:Size="8" ss:Bold="1"/>
   <Interior ss:Color="#FFFFCC" ss:Pattern="Solid"/>
   <NumberFormat ss:Format="00000"/>
  </Style>
  <Style ss:ID="s27">
   <Font x:Family="Swiss" ss:Size="8"/>
  </Style>
  <Style ss:ID="s28">
   <Font x:Family="Swiss" ss:Size="8"/>
   <NumberFormat ss:Format="00000"/>
  </Style>
  <Style ss:ID="s29">
   <Alignment ss:Horizontal="Right" ss:Vertical="Bottom"/>
   <Font x:Family="Swiss" ss:Size="8" ss:Bold="1"/>
   <Interior ss:Color="#FFFFCC" ss:Pattern="Solid"/>
  </Style>
  <Style ss:ID="s32">
   <Font ss:Size="8" ss:Color="#0000FF" ss:Underline="Single"/>
  </Style>
  <Style ss:ID="s33">
   <Font x:Family="Swiss" ss:Size="8" ss:Bold="1"/>
   <Alignment ss:Horizontal="Right" ss:Vertical="Bottom"/>
   <Interior ss:Color="#FFFFCC" ss:Pattern="Solid"/>
   <NumberFormat/>
  </Style>
  <Style ss:ID="s34">
   <Font x:Family="Swiss" ss:Size="8"/>
   <NumberFormat/>
  </Style>
  <Style ss:ID="s35">
   <Font x:Family="Swiss" ss:Size="8" ss:Bold="1"/>
   <Alignment ss:Horizontal="Center" ss:Vertical="Bottom"/>
   <Interior ss:Color="#FFFFCC" ss:Pattern="Solid"/>
  </Style>
  <Style ss:ID="s36">
   <Font x:Family="Swiss" ss:Size="8"/>
   <Alignment ss:Horizontal="Center"/>
  </Style>
 </Styles>
 <Worksheet ss:Name="Sheet1">
  <Table ss:ExpandedColumnCount="32" ss:ExpandedRowCount="' . (db_found($result) + 1) . '" x:FullColumns="1" x:FullRows="1">
   <Column ss:Width="23.25"/>
   <Column ss:Width="67.5"/>
   <Column ss:Width="81"/>
   <Column ss:Width="98.25"/>
   <Column ss:Width="49.5"/>
   <Column ss:AutoFitWidth="0" ss:Width="191.25"/>
   <Column ss:AutoFitWidth="0" ss:Width="189.75"/>
   <Column ss:AutoFitWidth="0" ss:Width="270"/>
   <Column ss:AutoFitWidth="0" ss:Width="103.5"/>
   <Column ss:Width="73.5"/>
   <Column ss:Width="26.25"/>
   <Column ss:StyleID="s23" ss:Width="27.75"/>
   <Column ss:AutoFitWidth="0" ss:Width="104.25"/>
   <Column ss:Width="122.25"/>
   <Column ss:Width="63.75"/>
   <Column ss:Width="181.5"/>
   <Column ss:Width="32.25"/>
   <Row ss:AutoFitHeight="0" ss:Height="24" ss:StyleID="s24">
    <Cell ss:StyleID="s29"><Data ss:Type="String">ID</Data></Cell>
    <Cell ss:StyleID="s25"><Data ss:Type="String">Courtesy Title</Data></Cell>
    <Cell ss:StyleID="s25"><Data ss:Type="String">First Name</Data></Cell>
    <Cell ss:StyleID="s25"><Data ss:Type="String">Last Name</Data></Cell>
    <Cell ss:StyleID="s25"><Data ss:Type="String">Suffix</Data></Cell>
    <Cell ss:StyleID="s25"><Data ss:Type="String">Company</Data></Cell>
    <Cell ss:StyleID="s25"><Data ss:Type="String">Job Title</Data></Cell>
    <Cell ss:StyleID="s25"><Data ss:Type="String">Address 1</Data></Cell>
    <Cell ss:StyleID="s25"><Data ss:Type="String">Address 2</Data></Cell>
    <Cell ss:StyleID="s25"><Data ss:Type="String">City</Data></Cell>
    <Cell ss:StyleID="s25"><Data ss:Type="String">State</Data></Cell>
    <Cell ss:StyleID="s26"><Data ss:Type="String">Postal Code</Data></Cell>
    <Cell ss:StyleID="s25"><Data ss:Type="String">Phone</Data></Cell>
    <Cell ss:StyleID="s25"><Data ss:Type="String">Fax</Data></Cell>
    <Cell ss:StyleID="s25"><Data ss:Type="String">Cell</Data></Cell>
    <Cell ss:StyleID="s25"><Data ss:Type="String">E-mail Address</Data></Cell>
    <Cell ss:StyleID="s33"><Data ss:Type="String"># Tags</Data></Cell>
    <Cell ss:StyleID="s35"><Data ss:Type="String">Asset Building</Data></Cell>
    <Cell ss:StyleID="s35"><Data ss:Type="String">Community Finance</Data></Cell>
    <Cell ss:StyleID="s35"><Data ss:Type="String">Economic Development</Data></Cell>
    <Cell ss:StyleID="s35"><Data ss:Type="String">Funders</Data></Cell>
    <Cell ss:StyleID="s35"><Data ss:Type="String">Technical Assistance</Data></Cell>
    <Cell ss:StyleID="s35"><Data ss:Type="String">Workforce Development</Data></Cell>
    <Cell ss:StyleID="s35"><Data ss:Type="String">Higher Education</Data></Cell>
    <Cell ss:StyleID="s35"><Data ss:Type="String">For Profit Institution</Data></Cell>
    <Cell ss:StyleID="s35"><Data ss:Type="String">Government</Data></Cell>
    <Cell ss:StyleID="s35"><Data ss:Type="String">Media</Data></Cell>
    <Cell ss:StyleID="s35"><Data ss:Type="String">Nonprofit Institution</Data></Cell>
    <Cell ss:StyleID="s35"><Data ss:Type="String">Vendor/Consultant</Data></Cell>
    <Cell ss:StyleID="s35"><Data ss:Type="String">Seedco Board Member</Data></Cell>
    <Cell ss:StyleID="s35"><Data ss:Type="String">SFS Board Member</Data></Cell>
    <Cell ss:StyleID="s35"><Data ss:Type="String">Imported Contacts</Data></Cell>
   </Row>
   ';
   
	while ($r = db_fetch($result)) {
		//while (list($key, $value) = each($r)) $r[$key] = htmlentities($value);
   $return .= '
   <Row>
	<Cell ss:StyleID="s32" ss:HRef="' . $r["link"] . '"><Data ss:Type="Number">' . $r["id"] . '</Data></Cell>
	<Cell ss:StyleID="s27"><Data ss:Type="String">' . $r["salutation"] . '</Data></Cell>
	<Cell ss:StyleID="s27"><Data ss:Type="String">' . htmlentities($r["firstname"]) . '</Data></Cell>
	<Cell ss:StyleID="s27"><Data ss:Type="String">' . htmlentities($r["lastname"]) . '</Data></Cell>
	<Cell ss:StyleID="s27"><Data ss:Type="String">' . htmlentities($r["suffix"]) . '</Data></Cell>
	<Cell ss:StyleID="s27"><Data ss:Type="String">' . htmlentities($r["org"]) . '</Data></Cell>
	<Cell ss:StyleID="s27"><Data ss:Type="String">' . htmlentities($r["title"]) . '</Data></Cell>
	<Cell ss:StyleID="s27"><Data ss:Type="String">' . htmlentities($r["address1"]) . '</Data></Cell>
	<Cell ss:StyleID="s27"><Data ss:Type="String">' . htmlentities($r["address2"]) . '</Data></Cell>
	<Cell ss:StyleID="s27"><Data ss:Type="String">' . $r["city"] . '</Data></Cell>
	<Cell ss:StyleID="s27"><Data ss:Type="String">' . $r["state"] . '</Data></Cell>
	<Cell ss:StyleID="s28"><Data ss:Type="Number">' . $r["zip"] . '</Data></Cell>
	<Cell ss:StyleID="s27"><Data ss:Type="String">' . htmlentities($r["phone"]) . '</Data></Cell>
	<Cell ss:StyleID="s27"><Data ss:Type="String">' . htmlentities($r["fax"]) . '</Data></Cell>
	<Cell ss:StyleID="s27"><Data ss:Type="String">' . htmlentities($r["cell"]) . '</Data></Cell>
	<Cell ss:StyleID="s27"><Data ss:Type="String">' . htmlentities($r["email"]) . '</Data></Cell>
	<Cell ss:StyleID="s34"><Data ss:Type="Number">' . $r["tagcount"] . '</Data></Cell>
	<Cell ss:StyleID="s36"><Data ss:Type="String">' . format_boolean($r["tagAsset"]) . '</Data></Cell>
	<Cell ss:StyleID="s36"><Data ss:Type="String">' . format_boolean($r["tagComFin"]) . '</Data></Cell>
	<Cell ss:StyleID="s36"><Data ss:Type="String">' . format_boolean($r["tagEconDev"]) . '</Data></Cell>
	<Cell ss:StyleID="s36"><Data ss:Type="String">' . format_boolean($r["tagFunders"]) . '</Data></Cell>
	<Cell ss:StyleID="s36"><Data ss:Type="String">' . format_boolean($r["tagTech"]) . '</Data></Cell>
	<Cell ss:StyleID="s36"><Data ss:Type="String">' . format_boolean($r["tagWorkforce"]) . '</Data></Cell>
	<Cell ss:StyleID="s36"><Data ss:Type="String">' . format_boolean($r["tagHigherEd"]) . '</Data></Cell>
	<Cell ss:StyleID="s36"><Data ss:Type="String">' . format_boolean($r["tagForProfit"]) . '</Data></Cell>
	<Cell ss:StyleID="s36"><Data ss:Type="String">' . format_boolean($r["tagGovt"]) . '</Data></Cell>
	<Cell ss:StyleID="s36"><Data ss:Type="String">' . format_boolean($r["tagMedia"]) . '</Data></Cell>
	<Cell ss:StyleID="s36"><Data ss:Type="String">' . format_boolean($r["tagNonProfit"]) . '</Data></Cell>
	<Cell ss:StyleID="s36"><Data ss:Type="String">' . format_boolean($r["tagVendor"]) . '</Data></Cell>
	<Cell ss:StyleID="s36"><Data ss:Type="String">' . format_boolean($r["tagSeedcoBoard"]) . '</Data></Cell>
	<Cell ss:StyleID="s36"><Data ss:Type="String">' . format_boolean($r["tagSFSBoard"]) . '</Data></Cell>
	<Cell ss:StyleID="s36"><Data ss:Type="String">' . format_boolean($r["tagImported"]) . '</Data></Cell>
   </Row>
   ';
	}
   $return .= '
  </Table>
  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <Print>
    <ValidPrinterInfo/>
    <HorizontalResolution>600</HorizontalResolution>
    <VerticalResolution>600</VerticalResolution>
   </Print>
   <Selected/>
   <FreezePanes/>
   <FrozenNoSplit/>
   <SplitHorizontal>1</SplitHorizontal>
   <TopRowBottomPane>1</TopRowBottomPane>
   <ActivePane>2</ActivePane>
   <Panes>
    <Pane>
     <Number>3</Number>
     <ActiveCol>1</ActiveCol>
    </Pane>
    <Pane>
     <Number>2</Number>
     <ActiveRow>16</ActiveRow>
     <ActiveCol>5</ActiveCol>
    </Pane>
   </Panes>
   <ProtectObjects>False</ProtectObjects>
   <ProtectScenarios>False</ProtectScenarios>
  </WorksheetOptions>
 </Worksheet>
</Workbook>';

//die($return);
file_download($return, "contact export", "xls");
?>