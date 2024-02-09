-- Adminer 4.8.1 PostgreSQL 16.1 (Ubuntu 16.1-1.pgdg22.04+1) dump

DROP TABLE IF EXISTS "bodys";
CREATE TABLE "public"."bodys" (
    "hash" character varying,
    "body" text,
    CONSTRAINT "bodys_hash" UNIQUE ("hash")
) WITH (oids = false);


DROP TABLE IF EXISTS "files";
DROP SEQUENCE IF EXISTS files_id_seq;
CREATE SEQUENCE files_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 2147483647 CACHE 1;

CREATE TABLE "public"."files" (
    "id" integer DEFAULT nextval('files_id_seq') NOT NULL,
    "file" character varying NOT NULL,
    "isdir" bit(1) DEFAULT '0' NOT NULL,
    CONSTRAINT "files_pkey" PRIMARY KEY ("id")
) WITH (oids = false);


DROP TABLE IF EXISTS "ips";
DROP SEQUENCE IF EXISTS ips_id_seq;
CREATE SEQUENCE ips_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 2147483647 CACHE 1;

CREATE TABLE "public"."ips" (
    "id" integer DEFAULT nextval('ips_id_seq') NOT NULL,
    "ip" inet NOT NULL,
    "updated_at" timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL,
    "host" character varying(254),
    "level" smallint DEFAULT '1' NOT NULL,
    "parent" integer,
    CONSTRAINT "ips_ip" UNIQUE ("ip"),
    CONSTRAINT "ips_pkey" PRIMARY KEY ("id")
) WITH (oids = false);

CREATE INDEX "ips_updated_at" ON "public"."ips" USING btree ("updated_at");


DROP VIEW IF EXISTS "molly";
CREATE TABLE "molly" ("url" text, "protos_id" integer, "ips_id" integer, "ports_id" integer, "files_id" integer, "body" text, "head" text, "status" smallint, "size" integer, "created_at" timestamp, "updated_at" timestamp, "proto" character varying(60), "ip" inet, "port" integer, "file" character varying, "isdir" bit(1));


DROP TABLE IF EXISTS "ports";
CREATE TABLE "public"."ports" (
    "id" integer NOT NULL,
    "enabled" smallint DEFAULT '0' NOT NULL,
    CONSTRAINT "ports_pkey" PRIMARY KEY ("id")
) WITH (oids = false);


DROP TABLE IF EXISTS "protos";
DROP SEQUENCE IF EXISTS protos_id_seq;
CREATE SEQUENCE protos_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 2147483647 CACHE 1;

CREATE TABLE "public"."protos" (
    "id" integer DEFAULT nextval('protos_id_seq') NOT NULL,
    "proto" character varying(60) NOT NULL,
    "options" character varying(60),
    "enabled" smallint DEFAULT '1' NOT NULL,
    CONSTRAINT "protos_pkey" PRIMARY KEY ("id")
) WITH (oids = false);


DROP TABLE IF EXISTS "results";
DROP SEQUENCE IF EXISTS results_id_seq;
CREATE SEQUENCE results_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 2147483647 CACHE 1;

CREATE TABLE "public"."results" (
    "id" integer DEFAULT nextval('results_id_seq') NOT NULL,
    "ips_id" integer NOT NULL,
    "ports_id" integer NOT NULL,
    "protos_id" integer NOT NULL,
    "bodys_hash" character varying,
    "created_at" timestamp DEFAULT now() NOT NULL,
    "updated_at" timestamp DEFAULT now() NOT NULL,
    "status" smallint,
    "head" text,
    "size" integer DEFAULT '0',
    "files_id" integer,
    "content-size" integer,
    "content-type" character varying(100),
    CONSTRAINT "results_ips_id_ports_id_protos_id_file_id" UNIQUE ("ips_id", "ports_id", "protos_id", "files_id"),
    CONSTRAINT "results_pkey" PRIMARY KEY ("id")
) WITH (oids = false);

CREATE INDEX "results_file_id" ON "public"."results" USING btree ("files_id");

CREATE INDEX "results_ports_id" ON "public"."results" USING btree ("ports_id");

CREATE INDEX "results_protos_id" ON "public"."results" USING btree ("protos_id");

CREATE INDEX "results_status" ON "public"."results" USING btree ("status");


DROP VIEW IF EXISTS "xN_index";
CREATE TABLE "xN_index" ("url" text, "protos_id" integer, "ips_id" integer, "ports_id" integer, "files_id" integer, "body" text, "head" text, "status" smallint, "size" integer, "created_at" timestamp, "updated_at" timestamp, "proto" character varying(60), "ip" inet, "port" integer, "file" character varying, "isdir" bit(1));


DROP VIEW IF EXISTS "x_index";
CREATE TABLE "x_index" ("url" text, "protos_id" integer, "ips_id" integer, "ports_id" integer, "files_id" integer, "body" text, "head" text, "status" smallint, "size" integer, "created_at" timestamp, "updated_at" timestamp, "proto" character varying(60), "ip" inet, "port" integer, "file" character varying, "isdir" bit(1));


DROP VIEW IF EXISTS "x_index_noncommon";
CREATE TABLE "x_index_noncommon" ("url" text, "protos_id" integer, "ips_id" integer, "ports_id" integer, "files_id" integer, "body" text, "head" text, "status" smallint, "size" integer, "created_at" timestamp, "updated_at" timestamp, "proto" character varying(60), "ip" inet, "port" integer, "file" character varying);


DROP VIEW IF EXISTS "x_sqlsyntax";
CREATE TABLE "x_sqlsyntax" ("url" text, "protos_id" integer, "ips_id" integer, "ports_id" integer, "files_id" integer, "body" text, "head" text, "status" smallint, "size" integer, "created_at" timestamp, "updated_at" timestamp, "proto" character varying(60), "ip" inet, "port" integer, "file" character varying);


ALTER TABLE ONLY "public"."results" ADD CONSTRAINT "results_bodys_hash_fkey" FOREIGN KEY (bodys_hash) REFERENCES bodys(hash) NOT DEFERRABLE;
ALTER TABLE ONLY "public"."results" ADD CONSTRAINT "results_file_id_fkey" FOREIGN KEY (files_id) REFERENCES files(id) NOT DEFERRABLE;
ALTER TABLE ONLY "public"."results" ADD CONSTRAINT "results_ips_id_fkey" FOREIGN KEY (ips_id) REFERENCES ips(id) NOT DEFERRABLE;
ALTER TABLE ONLY "public"."results" ADD CONSTRAINT "results_ports_id_fkey" FOREIGN KEY (ports_id) REFERENCES ports(id) NOT DEFERRABLE;
ALTER TABLE ONLY "public"."results" ADD CONSTRAINT "results_protos_id_fkey" FOREIGN KEY (protos_id) REFERENCES protos(id) NOT DEFERRABLE;

DROP TABLE IF EXISTS "molly";
CREATE VIEW "molly" AS SELECT concat(protos.proto, '://', ips.ip, ':', ports.id, files.file) AS url,
    results.protos_id,
    results.ips_id,
    results.ports_id,
    results.files_id,
    bodys.body,
    results.head,
    results.status,
    results.size,
    results.created_at,
    results.updated_at,
    protos.proto,
    ips.ip,
    ports.id AS port,
    files.file,
    files.isdir
   FROM results,
    protos,
    ips,
    ports,
    files,
    bodys
  WHERE ((results.protos_id = protos.id) AND (results.ips_id = ips.id) AND (results.ports_id = ports.id) AND (results.files_id = files.id) AND ((results.bodys_hash)::text = (bodys.hash)::text));

DROP TABLE IF EXISTS "xN_index";
CREATE VIEW "xN_index" AS SELECT url,
    protos_id,
    ips_id,
    ports_id,
    files_id,
    body,
    head,
    status,
    size,
    created_at,
    updated_at,
    proto,
    ip,
    port,
    file,
    isdir
   FROM molly
  WHERE ((body ~~* '%index of%'::text) AND (size > 600) AND ((isdir)::text = '1'::text))
  ORDER BY updated_at DESC;

DROP TABLE IF EXISTS "x_index";
CREATE VIEW "x_index" AS SELECT url,
    protos_id,
    ips_id,
    ports_id,
    files_id,
    body,
    head,
    status,
    size,
    created_at,
    updated_at,
    proto,
    ip,
    port,
    file,
    isdir
   FROM molly
  WHERE ((body ~~* '%index of%'::text) AND (size > 560) AND (files_id <> 32) AND (isdir = '1'::"bit"))
  ORDER BY created_at DESC;

DROP TABLE IF EXISTS "x_index_noncommon";
CREATE VIEW "x_index_noncommon" AS SELECT url,
    protos_id,
    ips_id,
    ports_id,
    files_id,
    body,
    head,
    status,
    size,
    created_at,
    updated_at,
    proto,
    ip,
    port,
    file
   FROM molly
  WHERE ((body ~~* '%index of /%'::text) AND (size > 560) AND (port <> 80) AND (port <> 443) AND (files_id <> 32))
  ORDER BY created_at DESC;

DROP TABLE IF EXISTS "x_sqlsyntax";
CREATE VIEW "x_sqlsyntax" AS SELECT url,
    protos_id,
    ips_id,
    ports_id,
    files_id,
    body,
    head,
    status,
    size,
    created_at,
    updated_at,
    proto,
    ip,
    port,
    file
   FROM molly
  WHERE (body ~~* '%your SQL syntax%'::text)
  ORDER BY created_at DESC;

-- 2024-01-27 14:39:00.525533+00
