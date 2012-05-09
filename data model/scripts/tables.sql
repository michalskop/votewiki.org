-- KohoVolit.eu Generación Cuarta
-- tables of module VOTEWIKI

CREATE TABLE votewiki_record (
  id serial primary key,
  division_id integer references division (id) ON DELETE cascade ON UPDATE cascade,
  "name" varchar not null,
  lang varchar not null references language (code) ON DELETE cascade ON UPDATE cascade,
  last_updated_on timestamp without time zone NOT NULL DEFAULT now(),
  unique (division_id,lang)
);

CREATE TABLE votewiki_text_kind (
  code varchar not null primary key,
  "name" varchar not null,
  description text
);

CREATE TABLE votewiki_text (
  votewiki_record_id integer not null references votewiki_record (id) ON DELETE cascade ON UPDATE cascade,
  votewiki_text_kind_code varchar not null references votewiki_text_kind (code) ON DELETE cascade ON UPDATE cascade,
  "text" text,
  text_data tsvector NOT NULL DEFAULT ''::tsvector,
  primary key (votewiki_record_id,votewiki_text_kind_code)
);

CREATE TABLE votewiki_tag (
  votewiki_record_id integer not null references votewiki_record (id) ON DELETE cascade ON UPDATE cascade,
  tag varchar not null,
  tag_data tsvector NOT NULL DEFAULT ''::tsvector,
  lang character varying NOT NULL,
  primary key (votewiki_record_id,tag),
  CONSTRAINT votewiki_tag_lang_fkey FOREIGN KEY (lang)
      REFERENCES language (code) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT votewiki_tag_votewiki_record_id_fkey FOREIGN KEY (votewiki_record_id)
      REFERENCES votewiki_record (id) MATCH SIMPLE
);

-- privileges on objects
grant select
	on table votewiki_record, votewiki_text_kind, votewiki_text, votewiki_tag
	to kv_user, kv_editor, kv_admin;
grant insert, update, delete, truncate
	on table votewiki_record, votewiki_text_kind, votewiki_text, votewiki_tag
	to kv_admin;
grant usage
	on sequence votewiki_record_id_seq
	to kv_admin;
