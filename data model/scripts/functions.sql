-- Function: division_from_source(character varying, character varying)

-- DROP FUNCTION division_from_source(character varying, character varying);

CREATE OR REPLACE FUNCTION division_from_source(parliament_code character varying, source_code_value character varying)
  RETURNS SETOF division AS
$BODY$
	SELECT * FROM division
	WHERE id IN
	(SELECT division_id FROM division_attribute
	WHERE name = 'source_code' AND value = $2)
	AND parliament_code = $1
$BODY$
  LANGUAGE sql STABLE
  COST 100
  ROWS 1000;
ALTER FUNCTION division_from_source(character varying, character varying)
  OWNER TO kohovolit;
