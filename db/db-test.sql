--
-- PostgreSQL database dump
--

-- Dumped from database version 9.6.5
-- Dumped by pg_dump version 9.6.5

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


--
-- Name: postgis; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS postgis WITH SCHEMA public;


--
-- Name: EXTENSION postgis; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON EXTENSION postgis IS 'PostGIS geometry, geography, and raster spatial types and functions';


--
-- Name: unaccent; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS unaccent WITH SCHEMA public;


--
-- Name: EXTENSION unaccent; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON EXTENSION unaccent IS 'text search dictionary that removes accents';


SET search_path = public, pg_catalog;

--
-- Name: frequency; Type: DOMAIN; Schema: public; Owner: -
--

CREATE DOMAIN frequency AS character varying
	CONSTRAINT frequency_check CHECK (((VALUE)::text = ANY (ARRAY[('once'::character varying)::text, ('daily'::character varying)::text, ('weekly'::character varying)::text, ('monthly'::character varying)::text, ('yearly'::character varying)::text])));


--
-- Name: days_in_month(date); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION days_in_month(check_date date) RETURNS integer
    LANGUAGE plpgsql IMMUTABLE
    AS $$
DECLARE
  first_of_month DATE := check_date - ((extract(day from check_date) - 1)||' days')::interval;
BEGIN
  RETURN extract(day from first_of_month + '1 month'::interval - first_of_month);
END;
$$;


--
-- Name: generate_recurrences(interval, date, date, date, date, integer, integer, integer); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION generate_recurrences(duration interval, original_start_date date, original_end_date date, range_start date, range_end date, repeat_month integer, repeat_week integer, repeat_day integer) RETURNS SETOF date
    LANGUAGE plpgsql IMMUTABLE
    AS $$
DECLARE
  start_date DATE := original_start_date;
  next_date DATE;
  intervals INT := FLOOR(intervals_between(original_start_date, range_start, duration));
  current_month INT;
  current_week INT;
BEGIN
  IF repeat_month IS NOT NULL THEN
    start_date := start_date + (((12 + repeat_month - cast(extract(month from start_date) as int)) % 12) || ' months')::interval;
  END IF;
  IF repeat_week IS NULL AND repeat_day IS NOT NULL THEN
    IF duration = '7 days'::interval THEN
      start_date := start_date + (((7 + repeat_day - cast(extract(dow from start_date) as int)) % 7) || ' days')::interval;
    ELSE
      start_date := start_date + (repeat_day - extract(day from start_date) || ' days')::interval;
    END IF;
  END IF;
  LOOP
    next_date := start_date + duration * intervals;
    IF repeat_week IS NOT NULL AND repeat_day IS NOT NULL THEN
      current_month := extract(month from next_date);
      next_date := next_date + (((7 + repeat_day - cast(extract(dow from next_date) as int)) % 7) || ' days')::interval;
      IF extract(month from next_date) != current_month THEN
        next_date := next_date - '7 days'::interval;
      END IF;
      IF repeat_week > 0 THEN
        current_week := CEIL(extract(day from next_date) / 7);
      ELSE
        current_week := -CEIL((1 + days_in_month(next_date) - extract(day from next_date)) / 7);
      END IF;
      next_date := next_date + (repeat_week - current_week) * '7 days'::interval;
    END IF;
    EXIT WHEN next_date > range_end;

    IF next_date >= range_start AND next_date >= original_start_date THEN
      RETURN NEXT next_date;
    END IF;

    if original_end_date IS NOT NULL AND range_start >= original_start_date + (duration*intervals) AND range_start <= original_end_date + (duration*intervals) THEN
      RETURN NEXT next_date;
    END IF;
    intervals := intervals + 1;
  END LOOP;
END;
$$;


--
-- Name: interval_for(frequency); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION interval_for(recurs frequency) RETURNS interval
    LANGUAGE plpgsql IMMUTABLE
    AS $$
BEGIN
  IF recurs = 'daily' THEN
    RETURN '1 day'::interval;
  ELSIF recurs = 'weekly' THEN
    RETURN '7 days'::interval;
  ELSIF recurs = 'monthly' THEN
    RETURN '1 month'::interval;
  ELSIF recurs = 'yearly' THEN
    RETURN '1 year'::interval;
  ELSE
    RAISE EXCEPTION 'Recurrence % not supported by generate_recurrences()', recurs;
  END IF;
END;
$$;


--
-- Name: intervals_between(date, date, interval); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION intervals_between(start_date date, end_date date, duration interval) RETURNS double precision
    LANGUAGE plpgsql IMMUTABLE
    AS $$
DECLARE
  count FLOAT := 0;
  multiplier INT := 512;
BEGIN
  IF start_date > end_date THEN
    RETURN 0;
  END IF;
  LOOP
    WHILE start_date + (count + multiplier) * duration < end_date LOOP
      count := count + multiplier;
    END LOOP;
    EXIT WHEN multiplier = 1;
    multiplier := multiplier / 2;
  END LOOP;
  count := count + (extract(epoch from end_date) - extract(epoch from (start_date + count * duration))) / (extract(epoch from end_date + duration) - extract(epoch from end_date))::int;
  RETURN count;
END
$$;


--
-- Name: pseudo_random_id_generator(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION pseudo_random_id_generator() RETURNS integer
    LANGUAGE plpgsql IMMUTABLE STRICT
    AS $$
                DECLARE
                    l1 int;
                    l2 int;
                    r1 int;
                    r2 int;
                    VALUE int;
                    i int:=0;
                BEGIN
                    VALUE:= nextval('pseudo_random_id_seq');
                    l1:= (VALUE >> 16) & 65535;
                    r1:= VALUE & 65535;
                    WHILE i < 3 LOOP
                        l2 := r1;
                        r2 := l1 # ((((1366 * r1 + 150889) % 714025) / 714025.0) * 32767)::int;
                        l1 := l2;
                        r1 := r2;
                        i := i + 1;
                    END LOOP;
                    RETURN ((r1 << 16) + l1);
                END;
            $$;


--
-- Name: random_id_generator(character varying, bigint); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION random_id_generator(table_name character varying, initial_range bigint) RETURNS bigint
    LANGUAGE plpgsql
    AS $$DECLARE
              rand_int INTEGER;
              count INTEGER := 1;
              statement TEXT;
            BEGIN
              WHILE count > 0 LOOP
                initial_range := initial_range * 10;

                rand_int := (RANDOM() * initial_range)::BIGINT + initial_range / 10;

                statement := CONCAT('SELECT count(id) FROM ', table_name, ' WHERE id = ', rand_int);

                EXECUTE statement;
                IF NOT FOUND THEN
                  count := 0;
                END IF;

              END LOOP;
              RETURN rand_int;
            END;
            $$;


SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: event_occurrence; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE event_occurrence (
    id integer NOT NULL,
    space_id integer NOT NULL,
    event_id integer NOT NULL,
    rule text,
    starts_on date,
    ends_on date,
    starts_at timestamp without time zone,
    ends_at timestamp without time zone,
    frequency frequency,
    separation integer DEFAULT 1 NOT NULL,
    count integer,
    until date,
    timezone_name text DEFAULT 'Etc/UTC'::text NOT NULL,
    status integer DEFAULT 1 NOT NULL,
    CONSTRAINT positive_separation CHECK ((separation > 0))
);


--
-- Name: recurrences_for(event_occurrence, timestamp without time zone, timestamp without time zone); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION recurrences_for(event event_occurrence, range_start timestamp without time zone, range_end timestamp without time zone) RETURNS SETOF date
    LANGUAGE plpgsql STABLE
    AS $$
DECLARE
  recurrence event_occurrence_recurrence;
  recurrences_start DATE := COALESCE(event.starts_at::date, event.starts_on);
  recurrences_end DATE := range_end;
  duration INTERVAL := interval_for(event.frequency) * event.separation;
  next_date DATE;
BEGIN
  IF event.until IS NOT NULL AND event.until < recurrences_end THEN
    recurrences_end := event.until;
  END IF;
  IF event.count IS NOT NULL AND recurrences_start + (event.count - 1) * duration < recurrences_end THEN
    recurrences_end := recurrences_start + (event.count - 1) * duration;
  END IF;

  FOR recurrence IN
    SELECT event_occurrence_recurrence.*
      FROM (SELECT NULL) AS foo
      LEFT JOIN event_occurrence_recurrence
        ON event_occurrence_id = event.id
  LOOP
    FOR next_date IN
      SELECT *
        FROM generate_recurrences(
          duration,
          recurrences_start,
          COALESCE(event.ends_at::date, event.ends_on),
          range_start::date,
          recurrences_end,
          recurrence.month,
          recurrence.week,
          recurrence.day
        )
    LOOP
      RETURN NEXT next_date;
    END LOOP;
  END LOOP;
  RETURN;
END;
$$;


--
-- Name: recurring_event_occurrence_for(timestamp without time zone, timestamp without time zone, character varying, integer); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION recurring_event_occurrence_for(range_start timestamp without time zone, range_end timestamp without time zone, time_zone character varying, event_occurrence_limit integer) RETURNS SETOF event_occurrence
    LANGUAGE plpgsql STABLE
    AS $$
            DECLARE
              event event_occurrence;
              original_date DATE;
              original_date_in_zone DATE;
              start_time TIME;
              start_time_in_zone TIME;
              next_date DATE;
              next_time_in_zone TIME;
              duration INTERVAL;
              time_offset INTERVAL;
              r_start DATE := (timezone('UTC', range_start) AT TIME ZONE time_zone)::DATE;
              r_end DATE := (timezone('UTC', range_end) AT TIME ZONE time_zone)::DATE;

              recurrences_start DATE := CASE WHEN r_start < range_start THEN r_start ELSE range_start END;
              recurrences_end DATE := CASE WHEN r_end > range_end THEN r_end ELSE range_end END;

              inc_interval INTERVAL := '2 hours'::INTERVAL;

              ext_start TIMESTAMP := range_start::TIMESTAMP - inc_interval;
              ext_end   TIMESTAMP := range_end::TIMESTAMP   + inc_interval;
            BEGIN
              FOR event IN
                SELECT *
                  FROM event_occurrence
                  WHERE
                    status > 0
                    AND
                    (
                      (frequency = 'once' AND
                      ((starts_on IS NOT NULL AND ends_on IS NOT NULL AND starts_on <= r_end AND ends_on >= r_start) OR
                       (starts_on IS NOT NULL AND starts_on <= r_end AND starts_on >= r_start) OR
                       (starts_at <= range_end AND ends_at >= range_start)))

                      OR

                      (
                        frequency <> 'once' AND
                        (
                          ( starts_on IS NOT NULL AND starts_on <= ext_end ) OR
                          ( starts_at IS NOT NULL AND starts_at <= ext_end )
                        ) AND (
                          (until IS NULL AND ends_at IS NULL AND ends_on IS NULL) OR
                          (until IS NOT NULL AND until >= ext_start) OR
                          (ends_on IS NOT NULL AND ends_on >= ext_start) OR
                          (ends_at IS NOT NULL AND ends_at >= ext_start)
                        )
                      )
                    )

              LOOP
                IF event.frequency = 'once' THEN
                  RETURN NEXT event;
                  CONTINUE;
                END IF;

                -- All-day event
                IF event.starts_on IS NOT NULL AND event.ends_on IS NULL THEN
                  original_date := event.starts_on;
                  duration := '1 day'::interval;
                -- Multi-day event
                ELSIF event.starts_on IS NOT NULL AND event.ends_on IS NOT NULL THEN
                  original_date := event.starts_on;
                  duration := timezone(time_zone, event.ends_on) - timezone(time_zone, event.starts_on);
                -- Timespan event
                ELSE
                  original_date := event.starts_at::date;
                  original_date_in_zone := (timezone('UTC', event.starts_at) AT TIME ZONE event.timezone_name)::date;
                  start_time := event.starts_at::time;
                  start_time_in_zone := (timezone('UTC', event.starts_at) AT time ZONE event.timezone_name)::time;
                  duration := event.ends_at - event.starts_at;
                END IF;

                IF event.count IS NOT NULL THEN
                  recurrences_start := original_date;
                END IF;

                FOR next_date IN
                  SELECT occurrence
                    FROM (
                      SELECT * FROM recurrences_for(event, recurrences_start, recurrences_end) AS occurrence
                      UNION SELECT original_date
                      LIMIT event.count
                    ) AS occurrences
                    WHERE
                      occurrence::date <= recurrences_end AND
                      (occurrence + duration)::date >= recurrences_start AND
                      occurrence NOT IN (SELECT date FROM event_occurrence_cancellation WHERE event_occurrence_id = event.id)
                    LIMIT event_occurrence_limit
                LOOP
                  -- All-day event
                  IF event.starts_on IS NOT NULL AND event.ends_on IS NULL THEN
                    CONTINUE WHEN next_date < r_start OR next_date > r_end;
                    event.starts_on := next_date;

                  -- Multi-day event
                  ELSIF event.starts_on IS NOT NULL AND event.ends_on IS NOT NULL THEN
                    event.starts_on := next_date;
                    CONTINUE WHEN event.starts_on > r_end;
                    event.ends_on := next_date + duration;
                    CONTINUE WHEN event.ends_on < r_start;

                  -- Timespan event
                  ELSE
                    next_time_in_zone := (timezone('UTC', (next_date + start_time)) at time zone event.timezone_name)::time;
                    time_offset := (original_date_in_zone + next_time_in_zone) - (original_date_in_zone + start_time_in_zone);
                    event.starts_at := next_date + start_time - time_offset;

                    CONTINUE WHEN event.starts_at > range_end;
                    event.ends_at := event.starts_at + duration;
                    CONTINUE WHEN event.ends_at < range_start;
                  END IF;

                  RETURN NEXT event;
                END LOOP;
              END LOOP;
              RETURN;
            END;
            $$;


--
-- Name: _mesoregiao; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE _mesoregiao (
    gid integer NOT NULL,
    id double precision,
    nm_meso character varying(100),
    cd_geocodu character varying(2),
    geom geometry(MultiPolygon,4326)
);


--
-- Name: _mesoregiao_gid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE _mesoregiao_gid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: _mesoregiao_gid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE _mesoregiao_gid_seq OWNED BY _mesoregiao.gid;


--
-- Name: _microregiao; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE _microregiao (
    gid integer NOT NULL,
    id double precision,
    nm_micro character varying(100),
    cd_geocodu character varying(2),
    geom geometry(MultiPolygon,4326)
);


--
-- Name: _microregiao_gid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE _microregiao_gid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: _microregiao_gid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE _microregiao_gid_seq OWNED BY _microregiao.gid;


--
-- Name: _municipios; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE _municipios (
    gid integer NOT NULL,
    id double precision,
    cd_geocodm character varying(20),
    nm_municip character varying(60),
    geom geometry(MultiPolygon,4326)
);


--
-- Name: _municipios_gid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE _municipios_gid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: _municipios_gid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE _municipios_gid_seq OWNED BY _municipios.gid;


--
-- Name: agent_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE agent_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: agent; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE agent (
    id integer DEFAULT nextval('agent_id_seq'::regclass) NOT NULL,
    parent_id integer,
    user_id integer NOT NULL,
    type smallint NOT NULL,
    name character varying(255) NOT NULL,
    location point,
    _geo_location geography,
    short_description text,
    long_description text,
    create_timestamp timestamp without time zone NOT NULL,
    status smallint NOT NULL,
    is_verified boolean DEFAULT false NOT NULL,
    public_location boolean,
    update_timestamp timestamp(0) without time zone,
    subsite_id integer
);


--
-- Name: COLUMN agent.location; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN agent.location IS 'type=POINT';


--
-- Name: agent_meta; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE agent_meta (
    object_id integer NOT NULL,
    key character varying(255) NOT NULL,
    value text,
    id integer NOT NULL
);


--
-- Name: agent_meta_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE agent_meta_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: agent_meta_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE agent_meta_id_seq OWNED BY agent_meta.id;


--
-- Name: agent_relation; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE agent_relation (
    id integer NOT NULL,
    agent_id integer NOT NULL,
    object_type character varying(255) NOT NULL,
    object_id integer NOT NULL,
    type character varying(64),
    has_control boolean DEFAULT false NOT NULL,
    create_timestamp timestamp without time zone,
    status smallint
);


--
-- Name: agent_relation_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE agent_relation_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: agent_relation_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE agent_relation_id_seq OWNED BY agent_relation.id;


--
-- Name: db_update; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE db_update (
    name character varying(255) NOT NULL,
    exec_time timestamp without time zone DEFAULT now() NOT NULL
);


--
-- Name: entity_revision; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE entity_revision (
    id integer NOT NULL,
    user_id integer,
    object_id integer NOT NULL,
    object_type character varying(255) NOT NULL,
    create_timestamp timestamp(0) without time zone NOT NULL,
    action character varying(255) NOT NULL,
    message text NOT NULL
);


--
-- Name: entity_revision_data; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE entity_revision_data (
    id integer NOT NULL,
    "timestamp" timestamp(0) without time zone NOT NULL,
    key character varying(255) NOT NULL,
    value text
);


--
-- Name: entity_revision_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE entity_revision_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: entity_revision_revision_data; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE entity_revision_revision_data (
    revision_id integer NOT NULL,
    revision_data_id integer NOT NULL
);


--
-- Name: event; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE event (
    id integer NOT NULL,
    project_id integer,
    name character varying(255) NOT NULL,
    short_description text NOT NULL,
    long_description text,
    rules text,
    create_timestamp timestamp without time zone NOT NULL,
    status smallint NOT NULL,
    agent_id integer,
    is_verified boolean DEFAULT false NOT NULL,
    type smallint NOT NULL,
    update_timestamp timestamp(0) without time zone,
    subsite_id integer
);


--
-- Name: event_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE event_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: event_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE event_id_seq OWNED BY event.id;


--
-- Name: event_meta; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE event_meta (
    key character varying(255) NOT NULL,
    object_id integer NOT NULL,
    value text,
    id integer NOT NULL
);


--
-- Name: event_meta_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE event_meta_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: event_meta_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE event_meta_id_seq OWNED BY event_meta.id;


--
-- Name: event_occurrence_cancellation; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE event_occurrence_cancellation (
    id integer NOT NULL,
    event_occurrence_id integer,
    date date
);


--
-- Name: event_occurrence_cancellation_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE event_occurrence_cancellation_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: event_occurrence_cancellation_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE event_occurrence_cancellation_id_seq OWNED BY event_occurrence_cancellation.id;


--
-- Name: event_occurrence_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE event_occurrence_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: event_occurrence_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE event_occurrence_id_seq OWNED BY event_occurrence.id;


--
-- Name: event_occurrence_recurrence; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE event_occurrence_recurrence (
    id integer NOT NULL,
    event_occurrence_id integer,
    month integer,
    day integer,
    week integer
);


--
-- Name: event_occurrence_recurrence_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE event_occurrence_recurrence_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: event_occurrence_recurrence_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE event_occurrence_recurrence_id_seq OWNED BY event_occurrence_recurrence.id;


--
-- Name: file_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE file_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: file; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE file (
    id integer DEFAULT nextval('file_id_seq'::regclass) NOT NULL,
    md5 character varying(32) NOT NULL,
    mime_type character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    object_type character varying(255) NOT NULL,
    object_id integer NOT NULL,
    create_timestamp timestamp without time zone DEFAULT now() NOT NULL,
    grp character varying(32) NOT NULL,
    description character varying(255),
    parent_id integer,
    path character varying(1024) DEFAULT NULL::character varying
);


--
-- Name: geo_division_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE geo_division_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: geo_division; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE geo_division (
    id integer DEFAULT nextval('geo_division_id_seq'::regclass) NOT NULL,
    parent_id integer,
    type character varying(32) NOT NULL,
    cod character varying(32),
    name character varying(128) NOT NULL,
    geom geometry,
    CONSTRAINT enforce_dims_geom CHECK ((st_ndims(geom) = 2)),
    CONSTRAINT enforce_geotype_geom CHECK (((geometrytype(geom) = 'MULTIPOLYGON'::text) OR (geom IS NULL))),
    CONSTRAINT enforce_srid_geom CHECK ((st_srid(geom) = 4326))
);


--
-- Name: metadata; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE metadata (
    object_id integer NOT NULL,
    object_type character varying(255) NOT NULL,
    key character varying(32) NOT NULL,
    value text
);


--
-- Name: metalist_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE metalist_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: metalist; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE metalist (
    id integer DEFAULT nextval('metalist_id_seq'::regclass) NOT NULL,
    object_type character varying(255) NOT NULL,
    object_id integer NOT NULL,
    grp character varying(32) NOT NULL,
    title character varying(255) NOT NULL,
    description text,
    value character varying(2048) NOT NULL,
    create_timestamp timestamp without time zone DEFAULT now() NOT NULL,
    "order" smallint
);


--
-- Name: notification_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE notification_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: notification; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE notification (
    id integer DEFAULT nextval('notification_id_seq'::regclass) NOT NULL,
    user_id integer NOT NULL,
    request_id integer,
    message text NOT NULL,
    create_timestamp timestamp without time zone DEFAULT now() NOT NULL,
    action_timestamp timestamp without time zone,
    status smallint NOT NULL
);


--
-- Name: notification_meta; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE notification_meta (
    id integer NOT NULL,
    object_id integer NOT NULL,
    key character varying(255) NOT NULL,
    value text
);


--
-- Name: notification_meta_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE notification_meta_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: occurrence_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE occurrence_id_seq
    START WITH 100000
    INCREMENT BY 1
    MINVALUE 100000
    NO MAXVALUE
    CACHE 1
    CYCLE;


--
-- Name: pcache_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE pcache_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: pcache; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE pcache (
    id integer DEFAULT nextval('pcache_id_seq'::regclass) NOT NULL,
    user_id integer NOT NULL,
    action character varying(255) NOT NULL,
    create_timestamp timestamp(0) without time zone NOT NULL,
    object_type character varying(255) NOT NULL,
    object_id integer
);


--
-- Name: project; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE project (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    short_description text,
    long_description text,
    create_timestamp timestamp without time zone NOT NULL,
    status smallint NOT NULL,
    agent_id integer,
    is_verified boolean DEFAULT false NOT NULL,
    type smallint NOT NULL,
    parent_id integer,
    registration_from timestamp without time zone,
    registration_to timestamp without time zone,
    registration_categories text,
    use_registrations boolean DEFAULT false NOT NULL,
    published_registrations boolean DEFAULT false NOT NULL,
    update_timestamp timestamp(0) without time zone,
    subsite_id integer
);


--
-- Name: project_event; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE project_event (
    id integer NOT NULL,
    event_id integer NOT NULL,
    project_id integer NOT NULL,
    type smallint NOT NULL,
    status smallint NOT NULL
);


--
-- Name: project_event_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE project_event_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: project_event_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE project_event_id_seq OWNED BY project_event.id;


--
-- Name: project_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE project_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: project_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE project_id_seq OWNED BY project.id;


--
-- Name: project_meta; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE project_meta (
    object_id integer NOT NULL,
    key character varying(255) NOT NULL,
    value text,
    id integer NOT NULL
);


--
-- Name: project_meta_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE project_meta_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: project_meta_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE project_meta_id_seq OWNED BY project_meta.id;


--
-- Name: pseudo_random_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE pseudo_random_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: registration; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE registration (
    id integer NOT NULL,
    project_id integer NOT NULL,
    category character varying(255),
    agent_id integer NOT NULL,
    create_timestamp timestamp without time zone DEFAULT now() NOT NULL,
    sent_timestamp timestamp without time zone,
    status integer NOT NULL,
    agents_data text,
    subsite_id integer
);


--
-- Name: registration_field_configuration; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE registration_field_configuration (
    id integer NOT NULL,
    project_id integer,
    title character varying(255) NOT NULL,
    description text,
    categories text,
    required boolean NOT NULL,
    field_type character varying(255) NOT NULL,
    field_options text NOT NULL,
    max_size text,
    display_order smallint DEFAULT 255
);


--
-- Name: COLUMN registration_field_configuration.categories; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN registration_field_configuration.categories IS '(DC2Type:array)';


--
-- Name: registration_field_configuration_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE registration_field_configuration_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: registration_file_configuration; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE registration_file_configuration (
    id integer NOT NULL,
    project_id integer,
    title character varying(255) NOT NULL,
    description text,
    required boolean NOT NULL,
    categories text,
    display_order smallint DEFAULT 255
);


--
-- Name: COLUMN registration_file_configuration.categories; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN registration_file_configuration.categories IS '(DC2Type:array)';


--
-- Name: registration_file_configuration_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE registration_file_configuration_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: registration_file_configuration_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE registration_file_configuration_id_seq OWNED BY registration_file_configuration.id;


--
-- Name: registration_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE registration_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: registration_meta; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE registration_meta (
    object_id integer DEFAULT pseudo_random_id_generator() NOT NULL,
    key character varying(255) NOT NULL,
    value text,
    id integer NOT NULL
);


--
-- Name: registration_meta_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE registration_meta_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: registration_meta_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE registration_meta_id_seq OWNED BY registration_meta.id;


--
-- Name: request_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE request_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: request; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE request (
    id integer DEFAULT nextval('request_id_seq'::regclass) NOT NULL,
    request_uid character varying(32) NOT NULL,
    requester_user_id integer NOT NULL,
    origin_type character varying(255) NOT NULL,
    origin_id integer NOT NULL,
    destination_type character varying(255) NOT NULL,
    destination_id integer NOT NULL,
    metadata text,
    type character varying(255) NOT NULL,
    create_timestamp timestamp without time zone DEFAULT now() NOT NULL,
    action_timestamp timestamp without time zone,
    status smallint NOT NULL
);


--
-- Name: revision_data_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE revision_data_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: role; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE role (
    id integer NOT NULL,
    usr_id integer,
    name character varying(32) NOT NULL,
    subsite_id integer
);


--
-- Name: role_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE role_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: role_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE role_id_seq OWNED BY role.id;


--
-- Name: seal; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE seal (
    id integer NOT NULL,
    agent_id integer NOT NULL,
    name character varying(255) NOT NULL,
    short_description text,
    long_description text,
    valid_period smallint NOT NULL,
    create_timestamp timestamp(0) without time zone NOT NULL,
    status smallint NOT NULL,
    certificate_text text,
    update_timestamp timestamp(0) without time zone,
    subsite_id integer
);


--
-- Name: seal_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE seal_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: seal_meta; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE seal_meta (
    id integer NOT NULL,
    object_id integer NOT NULL,
    key character varying(255) NOT NULL,
    value text
);


--
-- Name: seal_meta_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE seal_meta_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: seal_relation; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE seal_relation (
    id integer NOT NULL,
    seal_id integer,
    object_id integer NOT NULL,
    create_timestamp timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    status smallint,
    object_type character varying(255) NOT NULL,
    agent_id integer NOT NULL,
    owner_id integer,
    validate_date date,
    renovation_request boolean
);


--
-- Name: seal_relation_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE seal_relation_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: space; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE space (
    id integer NOT NULL,
    parent_id integer,
    location point,
    _geo_location geography,
    name character varying(255) NOT NULL,
    short_description text,
    long_description text,
    create_timestamp timestamp without time zone DEFAULT now() NOT NULL,
    status smallint NOT NULL,
    type smallint NOT NULL,
    agent_id integer,
    is_verified boolean DEFAULT false NOT NULL,
    public boolean DEFAULT false NOT NULL,
    update_timestamp timestamp(0) without time zone,
    subsite_id integer
);


--
-- Name: COLUMN space.location; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN space.location IS 'type=POINT';


--
-- Name: space_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE space_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: space_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE space_id_seq OWNED BY space.id;


--
-- Name: space_meta; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE space_meta (
    object_id integer NOT NULL,
    key character varying(255) NOT NULL,
    value text,
    id integer NOT NULL
);


--
-- Name: space_meta_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE space_meta_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: space_meta_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE space_meta_id_seq OWNED BY space_meta.id;


--
-- Name: subsite; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE subsite (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    create_timestamp timestamp(0) without time zone NOT NULL,
    status smallint NOT NULL,
    agent_id integer NOT NULL,
    url character varying(255) NOT NULL,
    namespace character varying(50) NOT NULL,
    alias_url character varying(255) DEFAULT NULL::character varying,
    verified_seals character varying(512) DEFAULT '[]'::character varying
);


--
-- Name: subsite_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE subsite_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: subsite_meta; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE subsite_meta (
    object_id integer NOT NULL,
    key character varying(255) NOT NULL,
    value text,
    id integer NOT NULL
);


--
-- Name: subsite_meta_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE subsite_meta_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: term; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE term (
    id integer NOT NULL,
    taxonomy character varying(64) NOT NULL,
    term character varying(255) NOT NULL,
    description text
);


--
-- Name: COLUMN term.taxonomy; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN term.taxonomy IS '1=tag';


--
-- Name: term_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE term_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: term_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE term_id_seq OWNED BY term.id;


--
-- Name: term_relation; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE term_relation (
    term_id integer NOT NULL,
    object_type character varying(255) NOT NULL,
    object_id integer NOT NULL,
    id integer NOT NULL
);


--
-- Name: term_relation_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE term_relation_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: term_relation_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE term_relation_id_seq OWNED BY term_relation.id;


--
-- Name: user_app; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE user_app (
    public_key character varying(64) NOT NULL,
    private_key character varying(128) NOT NULL,
    user_id integer NOT NULL,
    name text NOT NULL,
    status integer NOT NULL,
    create_timestamp timestamp without time zone NOT NULL,
    subsite_id integer
);


--
-- Name: user_meta; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE user_meta (
    object_id integer NOT NULL,
    key character varying(255) NOT NULL,
    value text,
    id integer NOT NULL
);


--
-- Name: user_meta_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE user_meta_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: usr_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE usr_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: usr; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE usr (
    id integer DEFAULT nextval('usr_id_seq'::regclass) NOT NULL,
    auth_provider smallint NOT NULL,
    auth_uid character varying(512) NOT NULL,
    email character varying(255) NOT NULL,
    last_login_timestamp timestamp without time zone NOT NULL,
    create_timestamp timestamp without time zone DEFAULT now() NOT NULL,
    status smallint NOT NULL,
    profile_id integer
);


--
-- Name: COLUMN usr.auth_provider; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usr.auth_provider IS '1=openid';


--
-- Name: _mesoregiao gid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY _mesoregiao ALTER COLUMN gid SET DEFAULT nextval('_mesoregiao_gid_seq'::regclass);


--
-- Name: _microregiao gid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY _microregiao ALTER COLUMN gid SET DEFAULT nextval('_microregiao_gid_seq'::regclass);


--
-- Name: _municipios gid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY _municipios ALTER COLUMN gid SET DEFAULT nextval('_municipios_gid_seq'::regclass);


--
-- Name: agent_relation id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY agent_relation ALTER COLUMN id SET DEFAULT nextval('agent_relation_id_seq'::regclass);


--
-- Name: event id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY event ALTER COLUMN id SET DEFAULT nextval('event_id_seq'::regclass);


--
-- Name: event_occurrence id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY event_occurrence ALTER COLUMN id SET DEFAULT nextval('event_occurrence_id_seq'::regclass);


--
-- Name: event_occurrence_cancellation id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY event_occurrence_cancellation ALTER COLUMN id SET DEFAULT nextval('event_occurrence_cancellation_id_seq'::regclass);


--
-- Name: event_occurrence_recurrence id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY event_occurrence_recurrence ALTER COLUMN id SET DEFAULT nextval('event_occurrence_recurrence_id_seq'::regclass);


--
-- Name: project id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY project ALTER COLUMN id SET DEFAULT nextval('project_id_seq'::regclass);


--
-- Name: project_event id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY project_event ALTER COLUMN id SET DEFAULT nextval('project_event_id_seq'::regclass);


--
-- Name: space id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY space ALTER COLUMN id SET DEFAULT nextval('space_id_seq'::regclass);


--
-- Name: term id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY term ALTER COLUMN id SET DEFAULT nextval('term_id_seq'::regclass);


--
-- Name: term_relation id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY term_relation ALTER COLUMN id SET DEFAULT nextval('term_relation_id_seq'::regclass);


--
-- Data for Name: _mesoregiao; Type: TABLE DATA; Schema: public; Owner: -
--

COPY _mesoregiao (gid, id, nm_meso, cd_geocodu, geom) FROM stdin;
\.


--
-- Name: _mesoregiao_gid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('_mesoregiao_gid_seq', 1, false);


--
-- Data for Name: _microregiao; Type: TABLE DATA; Schema: public; Owner: -
--

COPY _microregiao (gid, id, nm_micro, cd_geocodu, geom) FROM stdin;
\.


--
-- Name: _microregiao_gid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('_microregiao_gid_seq', 1, false);


--
-- Data for Name: _municipios; Type: TABLE DATA; Schema: public; Owner: -
--

COPY _municipios (gid, id, cd_geocodm, nm_municip, geom) FROM stdin;
\.


--
-- Name: _municipios_gid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('_municipios_gid_seq', 1, false);


--
-- Data for Name: agent; Type: TABLE DATA; Schema: public; Owner: -
--

COPY agent (id, parent_id, user_id, type, name, location, _geo_location, short_description, long_description, create_timestamp, status, is_verified, public_location, update_timestamp, subsite_id) FROM stdin;
5	\N	5	1	Staff User 1	(0,0)	0101000020E610000000000000000000000000000000000000	short description		2014-05-21 17:57:23	1	t	\N	2014-05-21 17:41:23	\N
6	\N	6	1	Staff User 2	(0,0)	0101000020E610000000000000000000000000000000000000	short description		2014-05-21 17:57:23	1	f	\N	2014-05-21 17:42:02	\N
1	\N	1	1	Super Admin 1	(0,0)	0101000020E610000000000000000000000000000000000000	short description		2014-05-21 17:57:23	1	t	\N	2014-05-21 17:45:03	\N
2	\N	2	1	Super Admin 2	(0,0)	0101000020E610000000000000000000000000000000000000	short description		2014-05-21 17:57:23	1	f	\N	2014-05-21 17:38:59	\N
3	\N	3	1	Admin 1	(-46.6451145999999994,-23.5461789999999986)	0101000020E610000008967E1D935247C011C30E63D28B37C0	short description		2014-05-21 17:57:23	1	t	f	2016-12-15 14:23:01	\N
4	\N	4	1	Admin 2	(-46.6587759000000233,-23.5367427000000013)	0101000020E610000050F2C8C4525447C0E3DD36F8678937C0	short description		2014-05-21 17:57:23	1	f	t	2016-12-15 14:25:49	\N
7	\N	7	1	Normal User 1	(-46.6569948999999724,-23.5335214999999991)	0101000020E6100000C091AC68185447C07E3672DD948837C0	short description		2014-05-21 17:57:23	1	t	t	2016-12-15 14:32:27	\N
8	\N	8	1	Normal User 2	(0,0)	0101000020E610000000000000000000000000000000000000	short description		2014-05-21 17:57:23	1	f	f	2016-12-15 14:34:07	\N
356	\N	10	1	New 1	(-46.6465663000000177,-23.5419763999999994)	0101000020E610000010993CAFC25247C02D3421F7BE8A37C0	descricao curta		2016-12-15 23:50:40	1	f	t	2016-12-15 23:51:40	\N
357	\N	11	1	New 2	(-46.6569977999999992,-23.5338080000000005)	0101000020E6100000A0450081185447C0DA571EA4A78837C0	curta		2016-12-15 23:52:04	1	f	f	2016-12-15 23:53:17	\N
\.


--
-- Name: agent_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('agent_id_seq', 357, true);


--
-- Data for Name: agent_meta; Type: TABLE DATA; Schema: public; Owner: -
--

COPY agent_meta (object_id, key, value, id) FROM stdin;
3	nomeCompleto	Administrador Um	2
3	emailPublico	admin@um.com	3
3	endereco	Rua Epitácio Pessoa, 11 , República, 01220-030, São Paulo, SP	4
3	En_CEP	01220-030	5
3	En_Nome_Logradouro	Rua Epitácio Pessoa	6
3	En_Num	11	7
3	En_Bairro	República	8
3	En_Municipio	São Paulo	9
3	En_Estado	SP	10
3	sentNotification	0	1
4	nomeCompleto	Administrador Dois	11
4	endereco	Rua Doutor Brasílio Machado, 123 , Santa Cecília, 01230-010, São Paulo, SP	12
4	En_CEP	01230-010	13
4	En_Nome_Logradouro	Rua Doutor Brasílio Machado	14
4	En_Num	123	15
4	En_Bairro	Santa Cecília	16
4	En_Municipio	São Paulo	17
4	En_Estado	SP	18
7	endereco	Rua Rosa e Silva, 11 , Santa Cecília, 01230-020, São Paulo, SP	19
7	En_CEP	01230-020	20
7	En_Nome_Logradouro	Rua Rosa e Silva	21
7	En_Num	11	22
7	En_Bairro	Santa Cecília	23
7	En_Municipio	São Paulo	24
7	En_Estado	SP	25
7	nomeCompleto	Usuário Normal Um	26
8	nomeCompleto	Usuário Comum Dois	27
356	origin_site	mapas.rafa	28
356	endereco	Rua Rego Freitas, 33 , República, 01220-010, São Paulo, SP	29
356	En_CEP	01220-010	30
356	En_Nome_Logradouro	Rua Rego Freitas	31
356	En_Num	33	32
356	En_Bairro	República	33
356	En_Municipio	São Paulo	34
356	En_Estado	SP	35
357	origin_site	mapas.rafa	36
357	endereco	Rua Azevedo Marques, 32 , Santa Cecília, 01230-030, São Paulo, SP	37
357	En_CEP	01230-030	38
357	En_Nome_Logradouro	Rua Azevedo Marques	39
357	En_Num	32	40
357	En_Bairro	Santa Cecília	41
357	En_Municipio	São Paulo	42
357	En_Estado	SP	43
5	sentNotification	18	44
6	sentNotification	20	45
2	sentNotification	24	46
1	sentNotification	28	47
\.


--
-- Name: agent_meta_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('agent_meta_id_seq', 47, true);


--
-- Data for Name: agent_relation; Type: TABLE DATA; Schema: public; Owner: -
--

COPY agent_relation (id, agent_id, object_type, object_id, type, has_control, create_timestamp, status) FROM stdin;
2	3	MapasCulturais\\Entities\\Event	7	group-admin	t	2016-12-15 19:16:46	1
77	356	MapasCulturais\\Entities\\Agent	357	group-admin	t	2016-12-15 23:53:25	1
79	8	MapasCulturais\\Entities\\Event	522	group-admin	t	2016-12-15 23:57:42	-5
80	5	MapasCulturais\\Entities\\Event	522	colegas	f	2016-12-15 23:58:05	-5
78	7	MapasCulturais\\Entities\\Event	522	group-admin	t	2016-12-15 23:57:31	1
\.


--
-- Name: agent_relation_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('agent_relation_id_seq', 80, true);


--
-- Data for Name: db_update; Type: TABLE DATA; Schema: public; Owner: -
--

COPY db_update (name, exec_time) FROM stdin;
0001	2013-12-20 21:39:23.035876
remove agents and spaces with error	2014-01-29 17:06:12.622215
remove agents and spaces with error - 2014-02-07	2014-02-10 19:01:40.686626
create-occurrence_id_seq	2014-04-03 19:41:34.861338
importa programação virada cultural	2014-04-30 18:51:21.983983
importa programação virada cultural - sesc	2014-05-01 01:00:30.210001
importa programação virada cultural - estado	2014-05-02 15:29:28.296556
remove eventos e espacos antigos da virada	2014-05-07 12:47:22.131586
programação virada cultural	2014-05-07 12:48:05.991509
new random id generator	2016-12-15 11:55:01.657548
migrate gender	2016-12-15 11:55:01.657548
create table user apps	2016-12-15 11:55:01.657548
create table user_meta	2016-12-15 11:55:01.657548
create seal and seal relation tables	2016-12-15 11:55:01.657548
resize entity meta key columns	2016-12-15 11:55:01.657548
create registration field configuration table	2016-12-15 11:55:01.657548
alter table registration_file_configuration add categories	2016-12-15 11:55:01.657548
verified seal migration	2016-12-15 11:55:01.657548
create update timestamp entities	2016-12-15 11:55:01.657548
update entities last_update_timestamp with user last log timestamp	2016-12-15 11:55:01.657548
Fix field options field type from registration field configuration	2016-12-15 11:55:01.657548
Created owner seal relation field	2016-12-15 11:55:01.657548
create avatar thumbs	2016-12-15 11:55:01.657548
alter tablel term taxonomy type	2017-09-07 16:06:06.869496
create saas tables	2017-09-07 16:06:06.869496
rename saas tables to subsite	2017-09-07 16:06:06.869496
remove parent_url and add alias_url	2017-09-07 16:06:06.869496
alter table role add column subsite_id	2017-09-07 16:06:06.869496
ADD columns subsite_id	2017-09-07 16:06:06.869496
remove subsite slug column	2017-09-07 16:06:06.869496
add subsite verified_seals column	2017-09-07 16:06:06.869496
create table pcache	2017-09-07 16:06:06.869496
function create pcache id sequence 2	2017-09-07 16:06:06.869496
Add field for maximum size from registration field configuration	2017-09-07 16:06:06.869496
Add notification type for compliant and suggestion messages	2017-09-07 16:06:06.869496
create entity revision tables	2017-09-07 16:06:06.869496
ALTER TABLE file ADD COLUMN path	2017-09-07 16:06:06.869496
*_meta drop all indexes again	2017-09-07 16:06:06.869496
recreate *_meta indexes	2017-09-07 16:06:06.869496
create seal relation renovation flag field	2017-09-07 16:06:06.869496
create seal relation validate date	2017-09-07 16:06:06.869496
update seal_relation set validate_date	2017-09-07 16:06:06.869496
refactor of entity meta keky value indexes	2017-09-07 16:06:06.869496
altertable registration_file_and_files_add_order	2017-09-07 16:06:06.869496
replace subsite entidades_habilitadas values	2017-09-07 16:06:06.869496
replace subsite cor entidades values	2017-09-07 16:06:06.869496
update taxonomy slug tag	2017-09-07 16:06:06.869496
update taxonomy slug area	2017-09-07 16:06:06.869496
update taxonomy slug linguagem	2017-09-07 16:06:06.869496
recreate pcache	2017-09-07 16:06:11.221523
generate file path	2017-09-07 16:06:11.224906
create entities history entries	2017-09-07 16:06:11.228284
create entities updated revision	2017-09-07 16:06:11.232262
fix update timestamp of revisioned entities	2017-09-07 16:06:11.23582
\.


--
-- Data for Name: entity_revision; Type: TABLE DATA; Schema: public; Owner: -
--

COPY entity_revision (id, user_id, object_id, object_type, create_timestamp, action, message) FROM stdin;
1	5	5	MapasCulturais\\Entities\\Agent	2014-05-21 17:57:23	created	Registro criado.
2	6	6	MapasCulturais\\Entities\\Agent	2014-05-21 17:57:23	created	Registro criado.
3	1	1	MapasCulturais\\Entities\\Agent	2014-05-21 17:57:23	created	Registro criado.
4	2	2	MapasCulturais\\Entities\\Agent	2014-05-21 17:57:23	created	Registro criado.
5	3	3	MapasCulturais\\Entities\\Agent	2014-05-21 17:57:23	created	Registro criado.
6	4	4	MapasCulturais\\Entities\\Agent	2014-05-21 17:57:23	created	Registro criado.
7	7	7	MapasCulturais\\Entities\\Agent	2014-05-21 17:57:23	created	Registro criado.
8	8	8	MapasCulturais\\Entities\\Agent	2014-05-21 17:57:23	created	Registro criado.
9	10	356	MapasCulturais\\Entities\\Agent	2016-12-15 23:50:40	created	Registro criado.
10	11	357	MapasCulturais\\Entities\\Agent	2016-12-15 23:52:04	created	Registro criado.
11	7	7	MapasCulturais\\Entities\\Space	2014-05-21 18:04:38	created	Registro criado.
12	8	8	MapasCulturais\\Entities\\Space	2014-05-21 18:04:38	created	Registro criado.
13	6	6	MapasCulturais\\Entities\\Space	2014-05-21 18:04:38	created	Registro criado.
14	5	5	MapasCulturais\\Entities\\Space	2014-05-21 18:04:38	created	Registro criado.
15	4	4	MapasCulturais\\Entities\\Space	2014-05-21 18:04:38	created	Registro criado.
16	3	3	MapasCulturais\\Entities\\Space	2014-05-21 18:04:38	created	Registro criado.
17	2	2	MapasCulturais\\Entities\\Space	2014-05-21 18:04:38	created	Registro criado.
18	1	1	MapasCulturais\\Entities\\Space	2014-05-21 18:04:38	created	Registro criado.
19	6	6	MapasCulturais\\Entities\\Event	2014-05-21 18:04:44	created	Registro criado.
20	8	8	MapasCulturais\\Entities\\Event	2014-05-21 18:04:44	created	Registro criado.
21	1	1	MapasCulturais\\Entities\\Event	2014-05-21 18:04:44	created	Registro criado.
22	2	2	MapasCulturais\\Entities\\Event	2014-05-21 18:04:44	created	Registro criado.
23	4	4	MapasCulturais\\Entities\\Event	2014-05-21 18:04:44	created	Registro criado.
24	3	3	MapasCulturais\\Entities\\Event	2014-05-21 18:04:44	created	Registro criado.
25	5	5	MapasCulturais\\Entities\\Event	2014-05-21 18:04:44	created	Registro criado.
26	7	7	MapasCulturais\\Entities\\Event	2014-05-21 18:04:44	created	Registro criado.
27	11	522	MapasCulturais\\Entities\\Event	2016-12-15 23:56:29	created	Registro criado.
28	5	5	MapasCulturais\\Entities\\Agent	2017-09-07 19:06:10	modified	Registro atualizado.
29	6	6	MapasCulturais\\Entities\\Agent	2017-09-07 19:06:10	modified	Registro atualizado.
30	1	1	MapasCulturais\\Entities\\Agent	2017-09-07 19:06:10	modified	Registro atualizado.
31	2	2	MapasCulturais\\Entities\\Agent	2017-09-07 19:06:10	modified	Registro atualizado.
32	3	3	MapasCulturais\\Entities\\Agent	2017-09-07 19:06:10	modified	Registro atualizado.
33	4	4	MapasCulturais\\Entities\\Agent	2017-09-07 19:06:10	modified	Registro atualizado.
34	7	7	MapasCulturais\\Entities\\Agent	2017-09-07 19:06:10	modified	Registro atualizado.
35	8	8	MapasCulturais\\Entities\\Agent	2017-09-07 19:06:10	modified	Registro atualizado.
36	11	357	MapasCulturais\\Entities\\Agent	2017-09-07 19:06:10	modified	Registro atualizado.
37	7	7	MapasCulturais\\Entities\\Space	2017-09-07 19:06:10	modified	Registro atualizado.
38	8	8	MapasCulturais\\Entities\\Space	2017-09-07 19:06:10	modified	Registro atualizado.
39	6	6	MapasCulturais\\Entities\\Space	2017-09-07 19:06:10	modified	Registro atualizado.
40	5	5	MapasCulturais\\Entities\\Space	2017-09-07 19:06:10	modified	Registro atualizado.
41	4	4	MapasCulturais\\Entities\\Space	2017-09-07 19:06:10	modified	Registro atualizado.
42	3	3	MapasCulturais\\Entities\\Space	2017-09-07 19:06:10	modified	Registro atualizado.
43	2	2	MapasCulturais\\Entities\\Space	2017-09-07 19:06:10	modified	Registro atualizado.
44	1	1	MapasCulturais\\Entities\\Space	2017-09-07 19:06:10	modified	Registro atualizado.
45	6	6	MapasCulturais\\Entities\\Event	2017-09-07 19:06:10	modified	Registro atualizado.
46	8	8	MapasCulturais\\Entities\\Event	2017-09-07 19:06:10	modified	Registro atualizado.
47	1	1	MapasCulturais\\Entities\\Event	2017-09-07 19:06:10	modified	Registro atualizado.
48	2	2	MapasCulturais\\Entities\\Event	2017-09-07 19:06:11	modified	Registro atualizado.
49	4	4	MapasCulturais\\Entities\\Event	2017-09-07 19:06:11	modified	Registro atualizado.
50	3	3	MapasCulturais\\Entities\\Event	2017-09-07 19:06:11	modified	Registro atualizado.
51	5	5	MapasCulturais\\Entities\\Event	2017-09-07 19:06:11	modified	Registro atualizado.
52	7	7	MapasCulturais\\Entities\\Event	2017-09-07 19:06:11	modified	Registro atualizado.
53	11	522	MapasCulturais\\Entities\\Event	2017-09-07 19:06:11	modified	Registro atualizado.
\.


--
-- Data for Name: entity_revision_data; Type: TABLE DATA; Schema: public; Owner: -
--

COPY entity_revision_data (id, "timestamp", key, value) FROM stdin;
1	2017-09-07 19:06:09	_type	1
2	2017-09-07 19:06:09	name	"Staff User 1"
3	2017-09-07 19:06:09	publicLocation	null
4	2017-09-07 19:06:09	location	{"latitude":"0","longitude":"0"}
5	2017-09-07 19:06:09	shortDescription	"short description"
6	2017-09-07 19:06:09	longDescription	""
7	2017-09-07 19:06:09	createTimestamp	{"date":"2014-05-21 17:57:23.000000","timezone_type":3,"timezone":"UTC"}
8	2017-09-07 19:06:09	status	1
9	2017-09-07 19:06:09	updateTimestamp	{"date":"2014-05-21 17:41:23.000000","timezone_type":3,"timezone":"UTC"}
10	2017-09-07 19:06:09	_subsiteId	null
11	2017-09-07 19:06:09	sentNotification	"18"
12	2017-09-07 19:06:09	_spaces	[{"id":5,"name":"Space 5","revision":0}]
13	2017-09-07 19:06:09	_events	[{"id":5,"name":"Event 5","revision":0}]
14	2017-09-07 19:06:09	_terms	{"":["Artesanato"]}
15	2017-09-07 19:06:09	_seals	[{"id":1,"name":"Selo Mapas","revision":0}]
16	2017-09-07 19:06:09	_type	1
17	2017-09-07 19:06:09	name	"Staff User 2"
18	2017-09-07 19:06:09	publicLocation	null
19	2017-09-07 19:06:09	location	{"latitude":"0","longitude":"0"}
20	2017-09-07 19:06:09	shortDescription	"short description"
21	2017-09-07 19:06:09	longDescription	""
22	2017-09-07 19:06:09	createTimestamp	{"date":"2014-05-21 17:57:23.000000","timezone_type":3,"timezone":"UTC"}
23	2017-09-07 19:06:09	status	1
24	2017-09-07 19:06:09	updateTimestamp	{"date":"2014-05-21 17:42:02.000000","timezone_type":3,"timezone":"UTC"}
25	2017-09-07 19:06:09	_subsiteId	null
26	2017-09-07 19:06:09	sentNotification	"20"
27	2017-09-07 19:06:09	_spaces	[{"id":6,"name":"Space 6","revision":0}]
28	2017-09-07 19:06:09	_events	[{"id":6,"name":"Event 6","revision":0}]
29	2017-09-07 19:06:09	_terms	{"":["Artes Visuais"]}
30	2017-09-07 19:06:09	_type	1
31	2017-09-07 19:06:09	name	"Super Admin 1"
32	2017-09-07 19:06:09	publicLocation	null
33	2017-09-07 19:06:09	location	{"latitude":"0","longitude":"0"}
34	2017-09-07 19:06:09	shortDescription	"short description"
35	2017-09-07 19:06:09	longDescription	""
36	2017-09-07 19:06:09	createTimestamp	{"date":"2014-05-21 17:57:23.000000","timezone_type":3,"timezone":"UTC"}
37	2017-09-07 19:06:09	status	1
38	2017-09-07 19:06:09	updateTimestamp	{"date":"2014-05-21 17:45:03.000000","timezone_type":3,"timezone":"UTC"}
39	2017-09-07 19:06:09	_subsiteId	null
40	2017-09-07 19:06:09	sentNotification	"28"
41	2017-09-07 19:06:09	_spaces	[{"id":1,"name":"Space 1","revision":0}]
42	2017-09-07 19:06:09	_events	[{"id":1,"name":"Event 1","revision":0}]
43	2017-09-07 19:06:09	_terms	{"":["Antropologia"]}
44	2017-09-07 19:06:09	_seals	[{"id":1,"name":"Selo Mapas","revision":0}]
45	2017-09-07 19:06:09	_type	1
46	2017-09-07 19:06:09	name	"Super Admin 2"
47	2017-09-07 19:06:09	publicLocation	null
48	2017-09-07 19:06:09	location	{"latitude":"0","longitude":"0"}
49	2017-09-07 19:06:09	shortDescription	"short description"
50	2017-09-07 19:06:09	longDescription	""
51	2017-09-07 19:06:09	createTimestamp	{"date":"2014-05-21 17:57:23.000000","timezone_type":3,"timezone":"UTC"}
52	2017-09-07 19:06:09	status	1
53	2017-09-07 19:06:09	updateTimestamp	{"date":"2014-05-21 17:38:59.000000","timezone_type":3,"timezone":"UTC"}
54	2017-09-07 19:06:09	_subsiteId	null
55	2017-09-07 19:06:09	sentNotification	"24"
56	2017-09-07 19:06:09	_spaces	[{"id":2,"name":"Space 2","revision":0}]
57	2017-09-07 19:06:09	_events	[{"id":2,"name":"Event 2","revision":0}]
58	2017-09-07 19:06:09	_terms	{"":["Arqueologia"]}
59	2017-09-07 19:06:09	_type	1
60	2017-09-07 19:06:09	name	"Admin 1"
61	2017-09-07 19:06:09	publicLocation	false
62	2017-09-07 19:06:09	location	{"latitude":"-23.546179","longitude":"-46.6451146"}
63	2017-09-07 19:06:09	shortDescription	"short description"
64	2017-09-07 19:06:09	longDescription	""
65	2017-09-07 19:06:09	createTimestamp	{"date":"2014-05-21 17:57:23.000000","timezone_type":3,"timezone":"UTC"}
66	2017-09-07 19:06:09	status	1
67	2017-09-07 19:06:09	updateTimestamp	{"date":"2016-12-15 14:23:01.000000","timezone_type":3,"timezone":"UTC"}
68	2017-09-07 19:06:09	_subsiteId	null
69	2017-09-07 19:06:09	nomeCompleto	"Administrador Um"
70	2017-09-07 19:06:09	emailPublico	"admin@um.com"
71	2017-09-07 19:06:09	endereco	"Rua Epit&aacute;cio Pessoa, 11 , Rep&uacute;blica, 01220-030, S&atilde;o Paulo, SP"
72	2017-09-07 19:06:09	En_CEP	"01220-030"
73	2017-09-07 19:06:09	En_Nome_Logradouro	"Rua Epit&aacute;cio Pessoa"
74	2017-09-07 19:06:09	En_Num	"11"
75	2017-09-07 19:06:09	En_Bairro	"Rep&uacute;blica"
76	2017-09-07 19:06:09	En_Municipio	"S&atilde;o Paulo"
77	2017-09-07 19:06:09	En_Estado	"SP"
78	2017-09-07 19:06:09	sentNotification	"0"
79	2017-09-07 19:06:09	_spaces	[{"id":3,"name":"Space 3","revision":0}]
80	2017-09-07 19:06:09	_events	[{"id":3,"name":"Event 3","revision":0}]
81	2017-09-07 19:06:09	_terms	{"":["Arquitetura-Urbanismo"]}
82	2017-09-07 19:06:09	_seals	[{"id":1,"name":"Selo Mapas","revision":0}]
83	2017-09-07 19:06:09	_type	1
84	2017-09-07 19:06:09	name	"Admin 2"
85	2017-09-07 19:06:09	publicLocation	true
86	2017-09-07 19:06:09	location	{"latitude":"-23.5367427","longitude":"-46.6587759"}
87	2017-09-07 19:06:09	shortDescription	"short description"
88	2017-09-07 19:06:09	longDescription	""
89	2017-09-07 19:06:09	createTimestamp	{"date":"2014-05-21 17:57:23.000000","timezone_type":3,"timezone":"UTC"}
90	2017-09-07 19:06:09	status	1
91	2017-09-07 19:06:09	updateTimestamp	{"date":"2016-12-15 14:25:49.000000","timezone_type":3,"timezone":"UTC"}
92	2017-09-07 19:06:09	_subsiteId	null
93	2017-09-07 19:06:09	nomeCompleto	"Administrador Dois"
94	2017-09-07 19:06:09	endereco	"Rua Doutor Bras&iacute;lio Machado, 123 , Santa Cec&iacute;lia, 01230-010, S&atilde;o Paulo, SP"
95	2017-09-07 19:06:09	En_CEP	"01230-010"
96	2017-09-07 19:06:09	En_Nome_Logradouro	"Rua Doutor Bras&iacute;lio Machado"
97	2017-09-07 19:06:09	En_Num	"123"
98	2017-09-07 19:06:09	En_Bairro	"Santa Cec&iacute;lia"
99	2017-09-07 19:06:09	En_Municipio	"S&atilde;o Paulo"
100	2017-09-07 19:06:09	En_Estado	"SP"
101	2017-09-07 19:06:09	_spaces	[{"id":4,"name":"Space 4","revision":0}]
102	2017-09-07 19:06:09	_events	[{"id":4,"name":"Event 4","revision":0}]
103	2017-09-07 19:06:09	_terms	{"":["Arquivo"]}
104	2017-09-07 19:06:09	_type	1
105	2017-09-07 19:06:09	name	"Normal User 1"
106	2017-09-07 19:06:09	publicLocation	true
107	2017-09-07 19:06:09	location	{"latitude":"-23.5335215","longitude":"-46.6569949"}
108	2017-09-07 19:06:09	shortDescription	"short description"
109	2017-09-07 19:06:09	longDescription	""
110	2017-09-07 19:06:09	createTimestamp	{"date":"2014-05-21 17:57:23.000000","timezone_type":3,"timezone":"UTC"}
111	2017-09-07 19:06:09	status	1
112	2017-09-07 19:06:09	updateTimestamp	{"date":"2016-12-15 14:32:27.000000","timezone_type":3,"timezone":"UTC"}
113	2017-09-07 19:06:09	_subsiteId	null
114	2017-09-07 19:06:09	endereco	"Rua Rosa e Silva, 11 , Santa Cec&iacute;lia, 01230-020, S&atilde;o Paulo, SP"
115	2017-09-07 19:06:09	En_CEP	"01230-020"
116	2017-09-07 19:06:09	En_Nome_Logradouro	"Rua Rosa e Silva"
117	2017-09-07 19:06:09	En_Num	"11"
118	2017-09-07 19:06:09	En_Bairro	"Santa Cec&iacute;lia"
119	2017-09-07 19:06:09	En_Municipio	"S&atilde;o Paulo"
120	2017-09-07 19:06:09	En_Estado	"SP"
121	2017-09-07 19:06:09	nomeCompleto	"Usu&aacute;rio Normal Um"
122	2017-09-07 19:06:09	_spaces	[{"id":7,"name":"Space 7","revision":0}]
123	2017-09-07 19:06:09	_events	[{"id":7,"name":"Event 7","revision":0}]
124	2017-09-07 19:06:09	_terms	{"":["Arquivo","Arte de Rua","Cinema"]}
125	2017-09-07 19:06:09	_seals	[{"id":1,"name":"Selo Mapas","revision":0}]
126	2017-09-07 19:06:09	_type	1
127	2017-09-07 19:06:09	name	"Normal User 2"
128	2017-09-07 19:06:09	publicLocation	false
129	2017-09-07 19:06:09	location	{"latitude":"0","longitude":"0"}
130	2017-09-07 19:06:09	shortDescription	"short description"
131	2017-09-07 19:06:09	longDescription	""
132	2017-09-07 19:06:09	createTimestamp	{"date":"2014-05-21 17:57:23.000000","timezone_type":3,"timezone":"UTC"}
133	2017-09-07 19:06:09	status	1
134	2017-09-07 19:06:09	updateTimestamp	{"date":"2016-12-15 14:34:07.000000","timezone_type":3,"timezone":"UTC"}
135	2017-09-07 19:06:09	_subsiteId	null
136	2017-09-07 19:06:09	nomeCompleto	"Usu&aacute;rio Comum Dois"
137	2017-09-07 19:06:09	_spaces	[{"id":8,"name":"Space 8","revision":0}]
138	2017-09-07 19:06:09	_events	[{"id":8,"name":"Event 8","revision":0}]
139	2017-09-07 19:06:09	_terms	{"":["Arquitetura-Urbanismo"]}
140	2017-09-07 19:06:09	_type	1
141	2017-09-07 19:06:09	name	"New 1"
142	2017-09-07 19:06:09	publicLocation	true
143	2017-09-07 19:06:09	location	{"latitude":"-23.5419764","longitude":"-46.6465663"}
144	2017-09-07 19:06:09	shortDescription	"descricao curta"
145	2017-09-07 19:06:09	longDescription	""
146	2017-09-07 19:06:09	createTimestamp	{"date":"2016-12-15 23:50:40.000000","timezone_type":3,"timezone":"UTC"}
147	2017-09-07 19:06:09	status	1
148	2017-09-07 19:06:09	updateTimestamp	{"date":"2016-12-15 23:51:40.000000","timezone_type":3,"timezone":"UTC"}
149	2017-09-07 19:06:09	_subsiteId	null
150	2017-09-07 19:06:09	origin_site	"mapas.rafa"
151	2017-09-07 19:06:09	endereco	"Rua Rego Freitas, 33 , Rep&uacute;blica, 01220-010, S&atilde;o Paulo, SP"
152	2017-09-07 19:06:09	En_CEP	"01220-010"
153	2017-09-07 19:06:09	En_Nome_Logradouro	"Rua Rego Freitas"
154	2017-09-07 19:06:09	En_Num	"33"
155	2017-09-07 19:06:09	En_Bairro	"Rep&uacute;blica"
156	2017-09-07 19:06:09	En_Municipio	"S&atilde;o Paulo"
157	2017-09-07 19:06:09	En_Estado	"SP"
158	2017-09-07 19:06:09	_terms	{"":["TAGUEADO","Arte Digital"]}
159	2017-09-07 19:06:09	_type	1
160	2017-09-07 19:06:09	name	"New 2"
161	2017-09-07 19:06:09	publicLocation	false
162	2017-09-07 19:06:09	location	{"latitude":"-23.533808","longitude":"-46.6569978"}
163	2017-09-07 19:06:09	shortDescription	"curta"
164	2017-09-07 19:06:09	longDescription	""
165	2017-09-07 19:06:09	createTimestamp	{"date":"2016-12-15 23:52:04.000000","timezone_type":3,"timezone":"UTC"}
166	2017-09-07 19:06:09	status	1
167	2017-09-07 19:06:09	updateTimestamp	{"date":"2016-12-15 23:53:17.000000","timezone_type":3,"timezone":"UTC"}
168	2017-09-07 19:06:09	_subsiteId	null
169	2017-09-07 19:06:09	origin_site	"mapas.rafa"
170	2017-09-07 19:06:09	endereco	"Rua Azevedo Marques, 32 , Santa Cec&iacute;lia, 01230-030, S&atilde;o Paulo, SP"
171	2017-09-07 19:06:09	En_CEP	"01230-030"
172	2017-09-07 19:06:09	En_Nome_Logradouro	"Rua Azevedo Marques"
173	2017-09-07 19:06:09	En_Num	"32"
174	2017-09-07 19:06:09	En_Bairro	"Santa Cec&iacute;lia"
175	2017-09-07 19:06:09	En_Municipio	"S&atilde;o Paulo"
176	2017-09-07 19:06:09	En_Estado	"SP"
177	2017-09-07 19:06:09	_events	[{"id":522,"name":"Novo Evento","revision":0}]
178	2017-09-07 19:06:09	_terms	{"":["TAGUEADO","Arte de Rua"]}
179	2017-09-07 19:06:09	_agents	{"group-admin":[{"id":356,"name":"New 1","revision":9}]}
180	2017-09-07 19:06:09	location	{"latitude":"-23.5394312","longitude":"-46.6915816"}
181	2017-09-07 19:06:09	name	"Space 7"
182	2017-09-07 19:06:09	public	false
183	2017-09-07 19:06:09	shortDescription	"of Normal User 1"
184	2017-09-07 19:06:09	longDescription	""
185	2017-09-07 19:06:09	createTimestamp	{"date":"2014-05-21 18:04:38.000000","timezone_type":3,"timezone":"UTC"}
186	2017-09-07 19:06:09	status	1
187	2017-09-07 19:06:09	_type	20
188	2017-09-07 19:06:09	_ownerId	7
189	2017-09-07 19:06:09	updateTimestamp	{"date":"2016-12-15 13:22:04.000000","timezone_type":3,"timezone":"UTC"}
190	2017-09-07 19:06:09	_subsiteId	null
191	2017-09-07 19:06:09	owner	{"id":7,"name":"Normal User 1","shortDescription":"short description","revision":7}
192	2017-09-07 19:06:09	acessibilidade	"N&atilde;o"
193	2017-09-07 19:06:09	endereco	"Rua Engenheiro Francisco Azevedo, 216 , Jardim Vera Cruz, 05030-010, S&atilde;o Paulo, SP"
194	2017-09-07 19:06:09	En_CEP	"05030-010"
195	2017-09-07 19:06:09	En_Nome_Logradouro	"Rua Engenheiro Francisco Azevedo"
196	2017-09-07 19:06:09	En_Num	"216"
197	2017-09-07 19:06:09	En_Bairro	"Jardim Vera Cruz"
198	2017-09-07 19:06:09	En_Municipio	"S&atilde;o Paulo"
199	2017-09-07 19:06:09	En_Estado	"SP"
200	2017-09-07 19:06:09	_terms	{"":["Arquitetura-Urbanismo","Artesanato"]}
201	2017-09-07 19:06:09	_seals	[{"id":1,"name":"Selo Mapas","revision":0}]
202	2017-09-07 19:06:09	location	{"latitude":"-23.5466151","longitude":"-46.6468627"}
203	2017-09-07 19:06:09	name	"Space 8"
204	2017-09-07 19:06:09	public	false
205	2017-09-07 19:06:09	shortDescription	"of Normal User 1"
206	2017-09-07 19:06:09	longDescription	""
207	2017-09-07 19:06:09	createTimestamp	{"date":"2014-05-21 18:04:38.000000","timezone_type":3,"timezone":"UTC"}
208	2017-09-07 19:06:09	status	1
209	2017-09-07 19:06:09	_type	22
210	2017-09-07 19:06:09	_ownerId	8
211	2017-09-07 19:06:09	updateTimestamp	{"date":"2016-12-15 13:22:16.000000","timezone_type":3,"timezone":"UTC"}
212	2017-09-07 19:06:09	_subsiteId	null
213	2017-09-07 19:06:10	owner	{"id":8,"name":"Normal User 2","shortDescription":"short description","revision":8}
214	2017-09-07 19:06:10	acessibilidade	"Sim"
215	2017-09-07 19:06:10	endereco	"Rua Rego Freitas, 530 , Rep&uacute;blica, 01220-010, S&atilde;o Paulo, SP"
216	2017-09-07 19:06:10	En_CEP	"01220-010"
217	2017-09-07 19:06:10	En_Nome_Logradouro	"Rua Rego Freitas"
218	2017-09-07 19:06:10	En_Num	"530"
219	2017-09-07 19:06:10	En_Bairro	"Rep&uacute;blica"
220	2017-09-07 19:06:10	En_Municipio	"S&atilde;o Paulo"
221	2017-09-07 19:06:10	En_Estado	"SP"
222	2017-09-07 19:06:10	acessibilidade_fisica	"Elevador;Rampa de acesso"
223	2017-09-07 19:06:10	_terms	{"":["Antropologia","M&uacute;sica"]}
224	2017-09-07 19:06:10	location	{"latitude":"-27.5887012","longitude":"-48.5070641"}
225	2017-09-07 19:06:10	name	"Space 6"
226	2017-09-07 19:06:10	public	false
227	2017-09-07 19:06:10	shortDescription	"of Staff User 2"
228	2017-09-07 19:06:10	longDescription	""
229	2017-09-07 19:06:10	createTimestamp	{"date":"2014-05-21 18:04:38.000000","timezone_type":3,"timezone":"UTC"}
230	2017-09-07 19:06:10	status	1
231	2017-09-07 19:06:10	_type	61
232	2017-09-07 19:06:10	_ownerId	6
233	2017-09-07 19:06:10	updateTimestamp	{"date":"2016-12-15 13:22:47.000000","timezone_type":3,"timezone":"UTC"}
234	2017-09-07 19:06:10	_subsiteId	null
235	2017-09-07 19:06:10	owner	{"id":6,"name":"Staff User 2","shortDescription":"short description","revision":2}
236	2017-09-07 19:06:10	acessibilidade	"Sim"
237	2017-09-07 19:06:10	En_CEP	"88035-001"
238	2017-09-07 19:06:10	En_Bairro	"Santa M&ocirc;nica"
239	2017-09-07 19:06:10	En_Municipio	"Florian&oacute;polis"
240	2017-09-07 19:06:10	En_Estado	"SC"
241	2017-09-07 19:06:10	endereco	"Avenida Madre Benvenuta, 1498 , Santa M&ocirc;nica, 88035-001, Florian&oacute;polis, SC"
242	2017-09-07 19:06:10	En_Nome_Logradouro	"Avenida Madre Benvenuta"
243	2017-09-07 19:06:10	En_Num	"1498"
244	2017-09-07 19:06:10	acessibilidade_fisica	"Rampa de acesso"
245	2017-09-07 19:06:10	_terms	{"":["Artesanato","Arte Digital"]}
246	2017-09-07 19:06:10	location	{"latitude":"-27.5666995","longitude":"-48.5102924"}
247	2017-09-07 19:06:10	name	"Space 5"
248	2017-09-07 19:06:10	public	false
249	2017-09-07 19:06:10	shortDescription	"of Staff User 1"
250	2017-09-07 19:06:10	longDescription	""
251	2017-09-07 19:06:10	createTimestamp	{"date":"2014-05-21 18:04:38.000000","timezone_type":3,"timezone":"UTC"}
252	2017-09-07 19:06:10	status	1
253	2017-09-07 19:06:10	_type	91
254	2017-09-07 19:06:10	_ownerId	5
255	2017-09-07 19:06:10	updateTimestamp	{"date":"2016-12-15 13:24:17.000000","timezone_type":3,"timezone":"UTC"}
256	2017-09-07 19:06:10	_subsiteId	null
257	2017-09-07 19:06:10	owner	{"id":5,"name":"Staff User 1","shortDescription":"short description","revision":1}
258	2017-09-07 19:06:10	endereco	"Rodovia Jos&eacute; Carlos Daux, 32 , Jo&atilde;o Paulo, 88030-000, Florian&oacute;polis, SC"
259	2017-09-07 19:06:10	En_CEP	"88030-000"
260	2017-09-07 19:06:10	En_Nome_Logradouro	"Rodovia Jos&eacute; Carlos Daux"
261	2017-09-07 19:06:10	En_Num	"32"
262	2017-09-07 19:06:10	En_Bairro	"Jo&atilde;o Paulo"
263	2017-09-07 19:06:10	En_Municipio	"Florian&oacute;polis"
264	2017-09-07 19:06:10	En_Estado	"SC"
265	2017-09-07 19:06:10	acessibilidade	"N&atilde;o"
266	2017-09-07 19:06:10	_terms	{"":["Cultura Negra","Circo","M&uacute;sica"]}
267	2017-09-07 19:06:10	_seals	[{"id":1,"name":"Selo Mapas","revision":0}]
268	2017-09-07 19:06:10	location	{"latitude":"-23.5575987","longitude":"-46.6499111"}
269	2017-09-07 19:06:10	name	"Space 4"
270	2017-09-07 19:06:10	public	false
271	2017-09-07 19:06:10	shortDescription	"of Admin 2"
272	2017-09-07 19:06:10	longDescription	""
273	2017-09-07 19:06:10	createTimestamp	{"date":"2014-05-21 18:04:38.000000","timezone_type":3,"timezone":"UTC"}
274	2017-09-07 19:06:10	status	1
275	2017-09-07 19:06:10	_type	60
276	2017-09-07 19:06:10	_ownerId	4
277	2017-09-07 19:06:10	updateTimestamp	{"date":"2016-12-15 13:27:44.000000","timezone_type":3,"timezone":"UTC"}
278	2017-09-07 19:06:10	_subsiteId	null
279	2017-09-07 19:06:10	owner	{"id":4,"name":"Admin 2","shortDescription":"short description","revision":6}
280	2017-09-07 19:06:10	acessibilidade	"Sim"
281	2017-09-07 19:06:10	acessibilidade_fisica	"Sinaliza&ccedil;&atilde;o t&aacute;til;Rampa de acesso;Vaga de estacionamento exclusiva para idosos;Elevador"
282	2017-09-07 19:06:10	endereco	"Rua Itapeva, 15 , Bela Vista, 01332-000, S&atilde;o Paulo, SP"
283	2017-09-07 19:06:10	En_CEP	"01332-000"
284	2017-09-07 19:06:10	En_Nome_Logradouro	"Rua Itapeva"
285	2017-09-07 19:06:10	En_Num	"15"
286	2017-09-07 19:06:10	En_Bairro	"Bela Vista"
287	2017-09-07 19:06:10	En_Municipio	"S&atilde;o Paulo"
288	2017-09-07 19:06:10	En_Estado	"SP"
289	2017-09-07 19:06:10	_terms	{"":["Artes Visuais","Fotografia","Arte Digital"]}
290	2017-09-07 19:06:10	location	{"latitude":"-23.5299146","longitude":"-46.6343522"}
291	2017-09-07 19:06:10	name	"Space 3"
292	2017-09-07 19:06:10	public	false
293	2017-09-07 19:06:10	shortDescription	"of Admin 1"
294	2017-09-07 19:06:10	longDescription	""
295	2017-09-07 19:06:10	createTimestamp	{"date":"2014-05-21 18:04:38.000000","timezone_type":3,"timezone":"UTC"}
296	2017-09-07 19:06:10	status	1
297	2017-09-07 19:06:10	_type	10
298	2017-09-07 19:06:10	_ownerId	3
299	2017-09-07 19:06:10	updateTimestamp	{"date":"2016-12-15 13:51:45.000000","timezone_type":3,"timezone":"UTC"}
300	2017-09-07 19:06:10	_subsiteId	null
301	2017-09-07 19:06:10	owner	{"id":3,"name":"Admin 1","shortDescription":"short description","revision":5}
302	2017-09-07 19:06:10	acessibilidade	"Sim"
303	2017-09-07 19:06:10	acessibilidade_fisica	"Elevador"
304	2017-09-07 19:06:10	endereco	"Rua Tr&ecirc;s Rios, 20 , Bom Retiro, 01123-000, S&atilde;o Paulo, SP"
305	2017-09-07 19:06:10	En_CEP	"01123-000"
306	2017-09-07 19:06:10	En_Nome_Logradouro	"Rua Tr&ecirc;s Rios"
307	2017-09-07 19:06:10	En_Num	"20"
308	2017-09-07 19:06:10	En_Bairro	"Bom Retiro"
309	2017-09-07 19:06:10	En_Municipio	"S&atilde;o Paulo"
310	2017-09-07 19:06:10	En_Estado	"SP"
311	2017-09-07 19:06:10	sentNotification	"0"
312	2017-09-07 19:06:10	_terms	{"":["Jogos Eletr&ocirc;nicos","Arte Digital"]}
313	2017-09-07 19:06:10	_seals	[{"id":1,"name":"Selo Mapas","revision":0}]
314	2017-09-07 19:06:10	location	{"latitude":"-27.5906075","longitude":"-48.5129766"}
315	2017-09-07 19:06:10	name	"Space 2"
316	2017-09-07 19:06:10	public	false
317	2017-09-07 19:06:10	shortDescription	"of Super Admin 2"
318	2017-09-07 19:06:10	longDescription	""
319	2017-09-07 19:06:10	createTimestamp	{"date":"2014-05-21 18:04:38.000000","timezone_type":3,"timezone":"UTC"}
320	2017-09-07 19:06:10	status	1
321	2017-09-07 19:06:10	_type	10
322	2017-09-07 19:06:10	_ownerId	2
323	2017-09-07 19:06:10	updateTimestamp	{"date":"2016-12-15 13:53:47.000000","timezone_type":3,"timezone":"UTC"}
324	2017-09-07 19:06:10	_subsiteId	null
325	2017-09-07 19:06:10	owner	{"id":2,"name":"Super Admin 2","shortDescription":"short description","revision":4}
326	2017-09-07 19:06:10	endereco	"Rua Tenente Jer&ocirc;nimo Borges, 33 , Santa M&ocirc;nica, 88035-050, Florian&oacute;polis, SC"
327	2017-09-07 19:06:10	En_CEP	"88035-050"
328	2017-09-07 19:06:10	En_Nome_Logradouro	"Rua Tenente Jer&ocirc;nimo Borges"
329	2017-09-07 19:06:10	En_Num	"33"
330	2017-09-07 19:06:10	En_Bairro	"Santa M&ocirc;nica"
331	2017-09-07 19:06:10	En_Municipio	"Florian&oacute;polis"
332	2017-09-07 19:06:10	En_Estado	"SC"
333	2017-09-07 19:06:10	acessibilidade	"N&atilde;o"
334	2017-09-07 19:06:10	acessibilidade_fisica	"Estacionamento"
335	2017-09-07 19:06:10	_terms	{"":["Circo","Arte de Rua"]}
336	2017-09-07 19:06:10	location	{"latitude":"-23.5443493","longitude":"-46.6444262"}
337	2017-09-07 19:06:10	name	"Space 1"
338	2017-09-07 19:06:10	public	false
339	2017-09-07 19:06:10	shortDescription	"of Super Admin 1"
340	2017-09-07 19:06:10	longDescription	""
341	2017-09-07 19:06:10	createTimestamp	{"date":"2014-05-21 18:04:38.000000","timezone_type":3,"timezone":"UTC"}
342	2017-09-07 19:06:10	status	1
343	2017-09-07 19:06:10	_type	10
344	2017-09-07 19:06:10	_ownerId	1
345	2017-09-07 19:06:10	updateTimestamp	{"date":"2016-12-15 14:20:58.000000","timezone_type":3,"timezone":"UTC"}
346	2017-09-07 19:06:10	_subsiteId	null
347	2017-09-07 19:06:10	owner	{"id":1,"name":"Super Admin 1","shortDescription":"short description","revision":3}
348	2017-09-07 19:06:10	endereco	"Rua Ara&uacute;jo, 22 , Rep&uacute;blica, 01220-020, S&atilde;o Paulo, SP"
349	2017-09-07 19:06:10	En_CEP	"01220-020"
350	2017-09-07 19:06:10	En_Nome_Logradouro	"Rua Ara&uacute;jo"
351	2017-09-07 19:06:10	En_Num	"22"
352	2017-09-07 19:06:10	En_Bairro	"Rep&uacute;blica"
353	2017-09-07 19:06:10	En_Municipio	"S&atilde;o Paulo"
354	2017-09-07 19:06:10	En_Estado	"SP"
355	2017-09-07 19:06:10	_terms	{"":["Jogos Eletr&ocirc;nicos","Filosofia","Esporte"]}
356	2017-09-07 19:06:10	_seals	[{"id":1,"name":"Selo Mapas","revision":0}]
357	2017-09-07 19:06:10	_type	1
358	2017-09-07 19:06:10	name	"Event 6"
359	2017-09-07 19:06:10	shortDescription	"of Staff User 2"
360	2017-09-07 19:06:10	longDescription	""
361	2017-09-07 19:06:10	rules	null
362	2017-09-07 19:06:10	createTimestamp	{"date":"2014-05-21 18:04:44.000000","timezone_type":3,"timezone":"UTC"}
363	2017-09-07 19:06:10	status	1
364	2017-09-07 19:06:10	updateTimestamp	{"date":"2014-05-21 17:42:02.000000","timezone_type":3,"timezone":"UTC"}
365	2017-09-07 19:06:10	_subsiteId	null
366	2017-09-07 19:06:10	owner	{"id":6,"name":"Staff User 2","shortDescription":"short description","revision":2}
367	2017-09-07 19:06:10	classificacaoEtaria             	"Livre"
368	2017-09-07 19:06:10	occurrences	{"1":{"items":[{"id":146,"description":"Dia 14 de dezembro de 2016 &agrave;s 11:11","_startsOn":null,"_endsOn":null,"_startsAt":null,"_endsAt":null,"frequency":"once","count":null,"_until":null,"rule":{"spaceId":"1","startsAt":"11:11","duration":11,"endsAt":"11:22","frequency":"once","startsOn":"2016-12-14","until":"","description":"Dia 14 de dezembro de 2016 \\u00e0s 11:11","price":"33"}},{"id":147,"description":"Dia 21 de dezembro de 2016 &agrave;s 13:00","_startsOn":null,"_endsOn":null,"_startsAt":null,"_endsAt":null,"frequency":"once","count":null,"_until":null,"rule":{"spaceId":"1","startsAt":"13:00","duration":213,"endsAt":"16:33","frequency":"once","startsOn":"2016-12-21","until":"","description":"Dia 21 de dezembro de 2016 \\u00e0s 13:00","price":"R$5,00"}}],"name":"Space 1","location":{"latitude":"-23.5443493","longitude":"-46.6444262"},"endereco":"Rua Ara&uacute;jo, 22 , Rep&uacute;blica, 01220-020, S&atilde;o Paulo, SP","revision":18}}
369	2017-09-07 19:06:10	_type	1
370	2017-09-07 19:06:10	name	"Event 8"
371	2017-09-07 19:06:10	shortDescription	"of Normal User 1"
372	2017-09-07 19:06:10	longDescription	""
373	2017-09-07 19:06:10	rules	null
374	2017-09-07 19:06:10	createTimestamp	{"date":"2014-05-21 18:04:44.000000","timezone_type":3,"timezone":"UTC"}
375	2017-09-07 19:06:10	status	1
376	2017-09-07 19:06:10	updateTimestamp	{"date":"2014-05-21 17:42:51.000000","timezone_type":3,"timezone":"UTC"}
377	2017-09-07 19:06:10	_subsiteId	null
378	2017-09-07 19:06:10	owner	{"id":8,"name":"Normal User 2","shortDescription":"short description","revision":8}
379	2017-09-07 19:06:10	classificacaoEtaria             	"Livre"
380	2017-09-07 19:06:10	_type	1
381	2017-09-07 19:06:10	name	"Event 1"
382	2017-09-07 19:06:10	shortDescription	"of Super Admin 1"
383	2017-09-07 19:06:10	longDescription	""
384	2017-09-07 19:06:10	rules	null
385	2017-09-07 19:06:10	createTimestamp	{"date":"2014-05-21 18:04:44.000000","timezone_type":3,"timezone":"UTC"}
386	2017-09-07 19:06:10	status	1
387	2017-09-07 19:06:10	updateTimestamp	{"date":"2014-05-21 17:45:03.000000","timezone_type":3,"timezone":"UTC"}
388	2017-09-07 19:06:10	_subsiteId	null
389	2017-09-07 19:06:10	owner	{"id":1,"name":"Super Admin 1","shortDescription":"short description","revision":3}
390	2017-09-07 19:06:10	classificacaoEtaria             	"Livre"
391	2017-09-07 19:06:10	_seals	[{"id":1,"name":"Selo Mapas","revision":0}]
392	2017-09-07 19:06:10	_type	1
393	2017-09-07 19:06:10	name	"Event 2"
394	2017-09-07 19:06:10	shortDescription	"of Super Admin 2"
395	2017-09-07 19:06:10	longDescription	""
396	2017-09-07 19:06:10	rules	null
397	2017-09-07 19:06:10	createTimestamp	{"date":"2014-05-21 18:04:44.000000","timezone_type":3,"timezone":"UTC"}
398	2017-09-07 19:06:10	status	1
399	2017-09-07 19:06:10	updateTimestamp	{"date":"2014-05-21 17:38:59.000000","timezone_type":3,"timezone":"UTC"}
400	2017-09-07 19:06:10	_subsiteId	null
401	2017-09-07 19:06:10	owner	{"id":2,"name":"Super Admin 2","shortDescription":"short description","revision":4}
402	2017-09-07 19:06:10	classificacaoEtaria             	"Livre"
403	2017-09-07 19:06:10	_type	1
404	2017-09-07 19:06:10	name	"Event 4"
405	2017-09-07 19:06:10	shortDescription	"of Admin 2"
406	2017-09-07 19:06:10	longDescription	""
407	2017-09-07 19:06:10	rules	null
408	2017-09-07 19:06:10	createTimestamp	{"date":"2014-05-21 18:04:44.000000","timezone_type":3,"timezone":"UTC"}
409	2017-09-07 19:06:10	status	1
410	2017-09-07 19:06:10	updateTimestamp	{"date":"2014-05-21 17:40:15.000000","timezone_type":3,"timezone":"UTC"}
411	2017-09-07 19:06:10	_subsiteId	null
412	2017-09-07 19:06:10	owner	{"id":4,"name":"Admin 2","shortDescription":"short description","revision":6}
413	2017-09-07 19:06:10	classificacaoEtaria             	"Livre"
414	2017-09-07 19:06:10	_type	1
415	2017-09-07 19:06:10	name	"Event 3"
416	2017-09-07 19:06:10	shortDescription	"of Admin 1"
417	2017-09-07 19:06:10	longDescription	""
418	2017-09-07 19:06:10	rules	null
419	2017-09-07 19:06:10	createTimestamp	{"date":"2014-05-21 18:04:44.000000","timezone_type":3,"timezone":"UTC"}
420	2017-09-07 19:06:10	status	1
421	2017-09-07 19:06:10	updateTimestamp	{"date":"2016-12-15 14:36:19.000000","timezone_type":3,"timezone":"UTC"}
422	2017-09-07 19:06:10	_subsiteId	null
423	2017-09-07 19:06:10	owner	{"id":3,"name":"Admin 1","shortDescription":"short description","revision":5}
424	2017-09-07 19:06:10	classificacaoEtaria             	"Livre"
425	2017-09-07 19:06:10	classificacaoEtaria	"Livre"
426	2017-09-07 19:06:10	_terms	{"":["Cinema"]}
427	2017-09-07 19:06:10	_seals	[{"id":1,"name":"Selo Mapas","revision":0}]
428	2017-09-07 19:06:10	occurrences	{"2":{"items":[{"id":142,"description":"Diariamente de 1 a 30 de dezembro de 2016 &agrave;s 10:00","_startsOn":null,"_endsOn":null,"_startsAt":null,"_endsAt":null,"frequency":"daily","count":null,"_until":null,"rule":{"spaceId":"2","startsAt":"10:00","duration":30,"endsAt":"10:30","frequency":"daily","startsOn":"2016-12-01","until":"2016-12-30","description":"Diariamente de 1 a 30 de dezembro de 2016 \\u00e0s 10:00","price":"Gratuito"}}],"name":"Space 2","location":{"latitude":"-27.5906075","longitude":"-48.5129766"},"endereco":"Rua Tenente Jer&ocirc;nimo Borges, 33 , Santa M&ocirc;nica, 88035-050, Florian&oacute;polis, SC","revision":17},"1":{"items":[{"id":143,"description":"Dia 16 de dezembro de 2016 &agrave;s 15:00","_startsOn":null,"_endsOn":null,"_startsAt":null,"_endsAt":null,"frequency":"once","count":null,"_until":null,"rule":{"spaceId":"1","startsAt":"15:00","duration":120,"endsAt":"17:00","frequency":"once","startsOn":"2016-12-16","until":"","description":"Dia 16 de dezembro de 2016 \\u00e0s 15:00","price":"Gratuito"}}],"name":"Space 1","location":{"latitude":"-23.5443493","longitude":"-46.6444262"},"endereco":"Rua Ara&uacute;jo, 22 , Rep&uacute;blica, 01220-020, S&atilde;o Paulo, SP","revision":18}}
429	2017-09-07 19:06:10	_type	1
430	2017-09-07 19:06:10	name	"Event 5"
431	2017-09-07 19:06:10	shortDescription	"of Staff User 1"
432	2017-09-07 19:06:10	longDescription	""
433	2017-09-07 19:06:10	rules	null
434	2017-09-07 19:06:10	createTimestamp	{"date":"2014-05-21 18:04:44.000000","timezone_type":3,"timezone":"UTC"}
435	2017-09-07 19:06:10	status	1
436	2017-09-07 19:06:10	updateTimestamp	{"date":"2016-12-15 14:39:22.000000","timezone_type":3,"timezone":"UTC"}
437	2017-09-07 19:06:10	_subsiteId	null
438	2017-09-07 19:06:10	owner	{"id":5,"name":"Staff User 1","shortDescription":"short description","revision":1}
439	2017-09-07 19:06:10	classificacaoEtaria             	"Livre"
440	2017-09-07 19:06:10	classificacaoEtaria	"14 anos"
441	2017-09-07 19:06:10	_terms	{"":["M&uacute;sica Popular"]}
442	2017-09-07 19:06:10	_seals	[{"id":1,"name":"Selo Mapas","revision":0}]
488	2017-09-07 19:06:10	_spaces	[{"id":7,"name":"Space 7","revision":11}]
489	2017-09-07 19:06:10	_events	[{"id":7,"name":"Event 7","revision":26}]
490	2017-09-07 19:06:10	_spaces	[{"id":8,"name":"Space 8","revision":12}]
491	2017-09-07 19:06:10	_events	[{"id":8,"name":"Event 8","revision":20}]
492	2017-09-07 19:06:10	_events	[{"id":522,"name":"Novo Evento","revision":27}]
493	2017-09-07 19:06:10	owner	{"id":7,"name":"Normal User 1","shortDescription":"short description","revision":34}
443	2017-09-07 19:06:10	occurrences	{"6":{"items":[{"id":144,"description":"Toda seg, qui e s&aacute;b de 1 de novembro de 2016 a 31 de janeiro de 2017 &agrave;s 08:00","_startsOn":null,"_endsOn":null,"_startsAt":null,"_endsAt":null,"frequency":"weekly","count":null,"_until":null,"rule":{"spaceId":"6","startsAt":"08:00","duration":5,"endsAt":"08:05","frequency":"weekly","startsOn":"2016-11-01","until":"2017-01-31","day":{"1":"on","4":"on","6":"on"},"description":"Toda seg, qui e s\\u00e1b de 1 de novembro de 2016 a 31 de janeiro de 2017 \\u00e0s 08:00","price":"R$5,00"}}],"name":"Space 6","location":{"latitude":"-27.5887012","longitude":"-48.5070641"},"endereco":"Avenida Madre Benvenuta, 1498 , Santa M&ocirc;nica, 88035-001, Florian&oacute;polis, SC","revision":13},"4":{"items":[{"id":145,"description":"Todo dom, seg, ter e qua de 1 a 29 de dezembro de 2016 &agrave;s 09:00","_startsOn":null,"_endsOn":null,"_startsAt":null,"_endsAt":null,"frequency":"weekly","count":null,"_until":null,"rule":{"spaceId":"4","startsAt":"09:00","duration":15,"endsAt":"09:15","frequency":"weekly","startsOn":"2016-12-01","until":"2016-12-29","day":["on","on","on","on"],"description":"Todo dom, seg, ter e qua de 1 a 29 de dezembro de 2016 \\u00e0s 09:00","price":"R$90,00"}}],"name":"Space 4","location":{"latitude":"-23.5575987","longitude":"-46.6499111"},"endereco":"Rua Itapeva, 15 , Bela Vista, 01332-000, S&atilde;o Paulo, SP","revision":15}}
444	2017-09-07 19:06:10	_type	1
445	2017-09-07 19:06:10	name	"Event 7"
446	2017-09-07 19:06:10	shortDescription	"of Normal User 1"
447	2017-09-07 19:06:10	longDescription	""
448	2017-09-07 19:06:10	rules	null
449	2017-09-07 19:06:10	createTimestamp	{"date":"2014-05-21 18:04:44.000000","timezone_type":3,"timezone":"UTC"}
450	2017-09-07 19:06:10	status	1
451	2017-09-07 19:06:10	updateTimestamp	{"date":"2016-12-15 19:16:37.000000","timezone_type":3,"timezone":"UTC"}
452	2017-09-07 19:06:10	_subsiteId	null
453	2017-09-07 19:06:10	owner	{"id":7,"name":"Normal User 1","shortDescription":"short description","revision":7}
454	2017-09-07 19:06:10	classificacaoEtaria             	"Livre"
455	2017-09-07 19:06:10	classificacaoEtaria	"16 anos"
456	2017-09-07 19:06:10	_terms	{"":["Cultura Ind&iacute;gena","R&aacute;dio"]}
457	2017-09-07 19:06:10	_seals	[{"id":1,"name":"Selo Mapas","revision":0}]
458	2017-09-07 19:06:10	_agents	{"group-admin":[{"id":3,"name":"Admin 1","revision":5}]}
459	2017-09-07 19:06:10	occurrences	{"1":{"items":[{"id":148,"description":"Dia 1 de dezembro de 2016 &agrave; 01:00","_startsOn":null,"_endsOn":null,"_startsAt":null,"_endsAt":null,"frequency":"once","count":null,"_until":null,"rule":{"spaceId":"1","startsAt":"01:00","duration":1,"endsAt":"01:01","frequency":"once","startsOn":"2016-12-01","until":"","description":"Dia 1 de dezembro de 2016 \\u00e0 01:00","price":"33"}}],"name":"Space 1","location":{"latitude":"-23.5443493","longitude":"-46.6444262"},"endereco":"Rua Ara&uacute;jo, 22 , Rep&uacute;blica, 01220-020, S&atilde;o Paulo, SP","revision":18},"2":{"items":[{"id":149,"description":"Dia 2 de dezembro de 2016 &agrave;s 02:00","_startsOn":null,"_endsOn":null,"_startsAt":null,"_endsAt":null,"frequency":"once","count":null,"_until":null,"rule":{"spaceId":"2","startsAt":"02:00","duration":2,"endsAt":"02:02","frequency":"once","startsOn":"2016-12-02","until":"","description":"Dia 2 de dezembro de 2016 \\u00e0s 02:00","price":"12"}}],"name":"Space 2","location":{"latitude":"-27.5906075","longitude":"-48.5129766"},"endereco":"Rua Tenente Jer&ocirc;nimo Borges, 33 , Santa M&ocirc;nica, 88035-050, Florian&oacute;polis, SC","revision":17},"3":{"items":[{"id":150,"description":"Dia 3 de dezembro de 2016 &agrave;s 03","_startsOn":null,"_endsOn":null,"_startsAt":null,"_endsAt":null,"frequency":"once","count":null,"_until":null,"rule":{"spaceId":"3","startsAt":"03:00","duration":3,"endsAt":"03:03","frequency":"once","startsOn":"2016-12-03","until":"","description":"Dia 3 de dezembro de 2016 \\u00e0s 03","price":"3"}}],"name":"Space 3","location":{"latitude":"-23.5299146","longitude":"-46.6343522"},"endereco":"Rua Tr&ecirc;s Rios, 20 , Bom Retiro, 01123-000, S&atilde;o Paulo, SP","revision":16},"4":{"items":[{"id":151,"description":"Dia 4 de dezembro de 2016 &agrave;s 04:00","_startsOn":null,"_endsOn":null,"_startsAt":null,"_endsAt":null,"frequency":"once","count":null,"_until":null,"rule":{"spaceId":"4","startsAt":"04:00","duration":4,"endsAt":"04:04","frequency":"once","startsOn":"2016-12-04","until":"","description":"Dia 4 de dezembro de 2016 \\u00e0s 04:00","price":"4"}}],"name":"Space 4","location":{"latitude":"-23.5575987","longitude":"-46.6499111"},"endereco":"Rua Itapeva, 15 , Bela Vista, 01332-000, S&atilde;o Paulo, SP","revision":15},"5":{"items":[{"id":152,"description":"Dia 5 de dezembro de 2016 &agrave;s 05:00","_startsOn":null,"_endsOn":null,"_startsAt":null,"_endsAt":null,"frequency":"once","count":null,"_until":null,"rule":{"spaceId":"5","startsAt":"05:00","duration":5,"endsAt":"05:05","frequency":"once","startsOn":"2016-12-05","until":"","description":"Dia 5 de dezembro de 2016 \\u00e0s 05:00","price":"5"}}],"name":"Space 5","location":{"latitude":"-27.5666995","longitude":"-48.5102924"},"endereco":"Rodovia Jos&eacute; Carlos Daux, 32 , Jo&atilde;o Paulo, 88030-000, Florian&oacute;polis, SC","revision":14},"6":{"items":[{"id":153,"description":"Dia 6 de dezembro de 2016 &agrave;s 06:00","_startsOn":null,"_endsOn":null,"_startsAt":null,"_endsAt":null,"frequency":"once","count":null,"_until":null,"rule":{"spaceId":"6","startsAt":"06:00","duration":6,"endsAt":"06:06","frequency":"once","startsOn":"2016-12-06","until":"","description":"Dia 6 de dezembro de 2016 \\u00e0s 06:00","price":"6"}}],"name":"Space 6","location":{"latitude":"-27.5887012","longitude":"-48.5070641"},"endereco":"Avenida Madre Benvenuta, 1498 , Santa M&ocirc;nica, 88035-001, Florian&oacute;polis, SC","revision":13},"7":{"items":[{"id":154,"description":"Dia 7 de dezembro de 2016 &agrave;s 07:00","_startsOn":null,"_endsOn":null,"_startsAt":null,"_endsAt":null,"frequency":"once","count":null,"_until":null,"rule":{"spaceId":"7","startsAt":"07:00","duration":7,"endsAt":"07:07","frequency":"once","startsOn":"2016-12-07","until":"","description":"Dia 7 de dezembro de 2016 \\u00e0s 07:00","price":"7"}}],"name":"Space 7","location":{"latitude":"-23.5394312","longitude":"-46.6915816"},"endereco":"Rua Engenheiro Francisco Azevedo, 216 , Jardim Vera Cruz, 05030-010, S&atilde;o Paulo, SP","revision":11},"8":{"items":[{"id":155,"description":"Dia 8 de dezembro de 2016 &agrave;s 08:00","_startsOn":null,"_endsOn":null,"_startsAt":null,"_endsAt":null,"frequency":"once","count":null,"_until":null,"rule":{"spaceId":"8","startsAt":"08:00","duration":8,"endsAt":"08:08","frequency":"once","startsOn":"2016-12-08","until":"","description":"Dia 8 de dezembro de 2016 \\u00e0s 08:00","price":"8"}}],"name":"Space 8","location":{"latitude":"-23.5466151","longitude":"-46.6468627"},"endereco":"Rua Rego Freitas, 530 , Rep&uacute;blica, 01220-010, S&atilde;o Paulo, SP","revision":12}}
460	2017-09-07 19:06:10	project	{"id":3,"name":"Project 3","revision":0}
461	2017-09-07 19:06:10	_type	1
462	2017-09-07 19:06:10	name	"Novo Evento"
463	2017-09-07 19:06:10	shortDescription	"pequeno evento"
464	2017-09-07 19:06:10	longDescription	""
465	2017-09-07 19:06:10	rules	null
466	2017-09-07 19:06:10	createTimestamp	{"date":"2016-12-15 23:56:29.000000","timezone_type":3,"timezone":"UTC"}
467	2017-09-07 19:06:10	status	1
468	2017-09-07 19:06:10	updateTimestamp	{"date":"2016-12-15 23:56:33.000000","timezone_type":3,"timezone":"UTC"}
469	2017-09-07 19:06:10	_subsiteId	null
470	2017-09-07 19:06:10	owner	{"id":357,"name":"New 2","shortDescription":"curta","revision":10}
471	2017-09-07 19:06:10	classificacaoEtaria	"Livre"
472	2017-09-07 19:06:10	origin_site	"mapas.rafa"
473	2017-09-07 19:06:10	_terms	{"":["Cinema","Artes Circenses"]}
474	2017-09-07 19:06:10	_agents	{"colegas":[{"id":5,"name":"Staff User 1","revision":1}],"group-admin":[{"id":7,"name":"Normal User 1","revision":7},{"id":8,"name":"Normal User 2","revision":8}]}
475	2017-09-07 19:06:10	occurrences	{"1":{"items":[{"id":162,"description":"Dia 21 de dezembro de 2016 &agrave;s 11:11","_startsOn":null,"_endsOn":null,"_startsAt":null,"_endsAt":null,"frequency":"once","count":null,"_until":null,"rule":{"spaceId":"1","startsAt":"11:11","duration":11,"endsAt":"11:22","frequency":"once","startsOn":"2016-12-21","until":"","description":"Dia 21 de dezembro de 2016 \\u00e0s 11:11","price":"gratuito"}}],"name":"Space 1","location":{"latitude":"-23.5443493","longitude":"-46.6444262"},"endereco":"Rua Ara&uacute;jo, 22 , Rep&uacute;blica, 01220-020, S&atilde;o Paulo, SP","revision":18},"2":{"items":[{"id":163,"description":"Dia 30 de dezembro de 2016 &agrave;s 22:22","_startsOn":null,"_endsOn":null,"_startsAt":null,"_endsAt":null,"frequency":"once","count":null,"_until":null,"rule":{"spaceId":"2","startsAt":"22:22","duration":22,"endsAt":"22:44","frequency":"once","startsOn":"2016-12-30","until":"","description":"Dia 30 de dezembro de 2016 \\u00e0s 22:22","price":"R$ 1,00"}}],"name":"Space 2","location":{"latitude":"-27.5906075","longitude":"-48.5129766"},"endereco":"Rua Tenente Jer&ocirc;nimo Borges, 33 , Santa M&ocirc;nica, 88035-050, Florian&oacute;polis, SC","revision":17}}
476	2017-09-07 19:06:10	_spaces	[{"id":5,"name":"Space 5","revision":14}]
477	2017-09-07 19:06:10	_events	[{"id":5,"name":"Event 5","revision":25}]
478	2017-09-07 19:06:10	_spaces	[{"id":6,"name":"Space 6","revision":13}]
479	2017-09-07 19:06:10	_events	[{"id":6,"name":"Event 6","revision":19}]
480	2017-09-07 19:06:10	_spaces	[{"id":1,"name":"Space 1","revision":18}]
481	2017-09-07 19:06:10	_events	[{"id":1,"name":"Event 1","revision":21}]
482	2017-09-07 19:06:10	_spaces	[{"id":2,"name":"Space 2","revision":17}]
483	2017-09-07 19:06:10	_events	[{"id":2,"name":"Event 2","revision":22}]
484	2017-09-07 19:06:10	_spaces	[{"id":3,"name":"Space 3","revision":16}]
485	2017-09-07 19:06:10	_events	[{"id":3,"name":"Event 3","revision":24}]
486	2017-09-07 19:06:10	_spaces	[{"id":4,"name":"Space 4","revision":15}]
487	2017-09-07 19:06:10	_events	[{"id":4,"name":"Event 4","revision":23}]
494	2017-09-07 19:06:10	owner	{"id":8,"name":"Normal User 2","shortDescription":"short description","revision":35}
495	2017-09-07 19:06:10	owner	{"id":6,"name":"Staff User 2","shortDescription":"short description","revision":29}
496	2017-09-07 19:06:10	owner	{"id":5,"name":"Staff User 1","shortDescription":"short description","revision":28}
497	2017-09-07 19:06:10	owner	{"id":4,"name":"Admin 2","shortDescription":"short description","revision":33}
498	2017-09-07 19:06:10	owner	{"id":3,"name":"Admin 1","shortDescription":"short description","revision":32}
499	2017-09-07 19:06:10	owner	{"id":2,"name":"Super Admin 2","shortDescription":"short description","revision":31}
500	2017-09-07 19:06:10	owner	{"id":1,"name":"Super Admin 1","shortDescription":"short description","revision":30}
501	2017-09-07 19:06:10	owner	{"id":6,"name":"Staff User 2","shortDescription":"short description","revision":29}
502	2017-09-07 19:06:10	occurrences	{"1":{"items":[{"id":146,"description":"Dia 14 de dezembro de 2016 &agrave;s 11:11","_startsOn":null,"_endsOn":null,"_startsAt":null,"_endsAt":null,"frequency":"once","count":null,"_until":null,"rule":{"spaceId":"1","startsAt":"11:11","duration":11,"endsAt":"11:22","frequency":"once","startsOn":"2016-12-14","until":"","description":"Dia 14 de dezembro de 2016 \\u00e0s 11:11","price":"33"}},{"id":147,"description":"Dia 21 de dezembro de 2016 &agrave;s 13:00","_startsOn":null,"_endsOn":null,"_startsAt":null,"_endsAt":null,"frequency":"once","count":null,"_until":null,"rule":{"spaceId":"1","startsAt":"13:00","duration":213,"endsAt":"16:33","frequency":"once","startsOn":"2016-12-21","until":"","description":"Dia 21 de dezembro de 2016 \\u00e0s 13:00","price":"R$5,00"}}],"name":"Space 1","location":{"latitude":"-23.5443493","longitude":"-46.6444262"},"endereco":"Rua Ara&uacute;jo, 22 , Rep&uacute;blica, 01220-020, S&atilde;o Paulo, SP","revision":44}}
503	2017-09-07 19:06:10	owner	{"id":8,"name":"Normal User 2","shortDescription":"short description","revision":35}
504	2017-09-07 19:06:10	owner	{"id":1,"name":"Super Admin 1","shortDescription":"short description","revision":30}
505	2017-09-07 19:06:11	owner	{"id":2,"name":"Super Admin 2","shortDescription":"short description","revision":31}
506	2017-09-07 19:06:11	owner	{"id":4,"name":"Admin 2","shortDescription":"short description","revision":33}
507	2017-09-07 19:06:11	owner	{"id":3,"name":"Admin 1","shortDescription":"short description","revision":32}
508	2017-09-07 19:06:11	occurrences	{"2":{"items":[{"id":142,"description":"Diariamente de 1 a 30 de dezembro de 2016 &agrave;s 10:00","_startsOn":null,"_endsOn":null,"_startsAt":null,"_endsAt":null,"frequency":"daily","count":null,"_until":null,"rule":{"spaceId":"2","startsAt":"10:00","duration":30,"endsAt":"10:30","frequency":"daily","startsOn":"2016-12-01","until":"2016-12-30","description":"Diariamente de 1 a 30 de dezembro de 2016 \\u00e0s 10:00","price":"Gratuito"}}],"name":"Space 2","location":{"latitude":"-27.5906075","longitude":"-48.5129766"},"endereco":"Rua Tenente Jer&ocirc;nimo Borges, 33 , Santa M&ocirc;nica, 88035-050, Florian&oacute;polis, SC","revision":43},"1":{"items":[{"id":143,"description":"Dia 16 de dezembro de 2016 &agrave;s 15:00","_startsOn":null,"_endsOn":null,"_startsAt":null,"_endsAt":null,"frequency":"once","count":null,"_until":null,"rule":{"spaceId":"1","startsAt":"15:00","duration":120,"endsAt":"17:00","frequency":"once","startsOn":"2016-12-16","until":"","description":"Dia 16 de dezembro de 2016 \\u00e0s 15:00","price":"Gratuito"}}],"name":"Space 1","location":{"latitude":"-23.5443493","longitude":"-46.6444262"},"endereco":"Rua Ara&uacute;jo, 22 , Rep&uacute;blica, 01220-020, S&atilde;o Paulo, SP","revision":44}}
509	2017-09-07 19:06:11	owner	{"id":5,"name":"Staff User 1","shortDescription":"short description","revision":28}
510	2017-09-07 19:06:11	occurrences	{"6":{"items":[{"id":144,"description":"Toda seg, qui e s&aacute;b de 1 de novembro de 2016 a 31 de janeiro de 2017 &agrave;s 08:00","_startsOn":null,"_endsOn":null,"_startsAt":null,"_endsAt":null,"frequency":"weekly","count":null,"_until":null,"rule":{"spaceId":"6","startsAt":"08:00","duration":5,"endsAt":"08:05","frequency":"weekly","startsOn":"2016-11-01","until":"2017-01-31","day":{"1":"on","4":"on","6":"on"},"description":"Toda seg, qui e s\\u00e1b de 1 de novembro de 2016 a 31 de janeiro de 2017 \\u00e0s 08:00","price":"R$5,00"}}],"name":"Space 6","location":{"latitude":"-27.5887012","longitude":"-48.5070641"},"endereco":"Avenida Madre Benvenuta, 1498 , Santa M&ocirc;nica, 88035-001, Florian&oacute;polis, SC","revision":39},"4":{"items":[{"id":145,"description":"Todo dom, seg, ter e qua de 1 a 29 de dezembro de 2016 &agrave;s 09:00","_startsOn":null,"_endsOn":null,"_startsAt":null,"_endsAt":null,"frequency":"weekly","count":null,"_until":null,"rule":{"spaceId":"4","startsAt":"09:00","duration":15,"endsAt":"09:15","frequency":"weekly","startsOn":"2016-12-01","until":"2016-12-29","day":["on","on","on","on"],"description":"Todo dom, seg, ter e qua de 1 a 29 de dezembro de 2016 \\u00e0s 09:00","price":"R$90,00"}}],"name":"Space 4","location":{"latitude":"-23.5575987","longitude":"-46.6499111"},"endereco":"Rua Itapeva, 15 , Bela Vista, 01332-000, S&atilde;o Paulo, SP","revision":41}}
511	2017-09-07 19:06:11	owner	{"id":7,"name":"Normal User 1","shortDescription":"short description","revision":34}
512	2017-09-07 19:06:11	_agents	{"group-admin":[{"id":3,"name":"Admin 1","revision":32}]}
513	2017-09-07 19:06:11	occurrences	{"1":{"items":[{"id":148,"description":"Dia 1 de dezembro de 2016 &agrave; 01:00","_startsOn":null,"_endsOn":null,"_startsAt":null,"_endsAt":null,"frequency":"once","count":null,"_until":null,"rule":{"spaceId":"1","startsAt":"01:00","duration":1,"endsAt":"01:01","frequency":"once","startsOn":"2016-12-01","until":"","description":"Dia 1 de dezembro de 2016 \\u00e0 01:00","price":"33"}}],"name":"Space 1","location":{"latitude":"-23.5443493","longitude":"-46.6444262"},"endereco":"Rua Ara&uacute;jo, 22 , Rep&uacute;blica, 01220-020, S&atilde;o Paulo, SP","revision":44},"2":{"items":[{"id":149,"description":"Dia 2 de dezembro de 2016 &agrave;s 02:00","_startsOn":null,"_endsOn":null,"_startsAt":null,"_endsAt":null,"frequency":"once","count":null,"_until":null,"rule":{"spaceId":"2","startsAt":"02:00","duration":2,"endsAt":"02:02","frequency":"once","startsOn":"2016-12-02","until":"","description":"Dia 2 de dezembro de 2016 \\u00e0s 02:00","price":"12"}}],"name":"Space 2","location":{"latitude":"-27.5906075","longitude":"-48.5129766"},"endereco":"Rua Tenente Jer&ocirc;nimo Borges, 33 , Santa M&ocirc;nica, 88035-050, Florian&oacute;polis, SC","revision":43},"3":{"items":[{"id":150,"description":"Dia 3 de dezembro de 2016 &agrave;s 03","_startsOn":null,"_endsOn":null,"_startsAt":null,"_endsAt":null,"frequency":"once","count":null,"_until":null,"rule":{"spaceId":"3","startsAt":"03:00","duration":3,"endsAt":"03:03","frequency":"once","startsOn":"2016-12-03","until":"","description":"Dia 3 de dezembro de 2016 \\u00e0s 03","price":"3"}}],"name":"Space 3","location":{"latitude":"-23.5299146","longitude":"-46.6343522"},"endereco":"Rua Tr&ecirc;s Rios, 20 , Bom Retiro, 01123-000, S&atilde;o Paulo, SP","revision":42},"4":{"items":[{"id":151,"description":"Dia 4 de dezembro de 2016 &agrave;s 04:00","_startsOn":null,"_endsOn":null,"_startsAt":null,"_endsAt":null,"frequency":"once","count":null,"_until":null,"rule":{"spaceId":"4","startsAt":"04:00","duration":4,"endsAt":"04:04","frequency":"once","startsOn":"2016-12-04","until":"","description":"Dia 4 de dezembro de 2016 \\u00e0s 04:00","price":"4"}}],"name":"Space 4","location":{"latitude":"-23.5575987","longitude":"-46.6499111"},"endereco":"Rua Itapeva, 15 , Bela Vista, 01332-000, S&atilde;o Paulo, SP","revision":41},"5":{"items":[{"id":152,"description":"Dia 5 de dezembro de 2016 &agrave;s 05:00","_startsOn":null,"_endsOn":null,"_startsAt":null,"_endsAt":null,"frequency":"once","count":null,"_until":null,"rule":{"spaceId":"5","startsAt":"05:00","duration":5,"endsAt":"05:05","frequency":"once","startsOn":"2016-12-05","until":"","description":"Dia 5 de dezembro de 2016 \\u00e0s 05:00","price":"5"}}],"name":"Space 5","location":{"latitude":"-27.5666995","longitude":"-48.5102924"},"endereco":"Rodovia Jos&eacute; Carlos Daux, 32 , Jo&atilde;o Paulo, 88030-000, Florian&oacute;polis, SC","revision":40},"6":{"items":[{"id":153,"description":"Dia 6 de dezembro de 2016 &agrave;s 06:00","_startsOn":null,"_endsOn":null,"_startsAt":null,"_endsAt":null,"frequency":"once","count":null,"_until":null,"rule":{"spaceId":"6","startsAt":"06:00","duration":6,"endsAt":"06:06","frequency":"once","startsOn":"2016-12-06","until":"","description":"Dia 6 de dezembro de 2016 \\u00e0s 06:00","price":"6"}}],"name":"Space 6","location":{"latitude":"-27.5887012","longitude":"-48.5070641"},"endereco":"Avenida Madre Benvenuta, 1498 , Santa M&ocirc;nica, 88035-001, Florian&oacute;polis, SC","revision":39},"7":{"items":[{"id":154,"description":"Dia 7 de dezembro de 2016 &agrave;s 07:00","_startsOn":null,"_endsOn":null,"_startsAt":null,"_endsAt":null,"frequency":"once","count":null,"_until":null,"rule":{"spaceId":"7","startsAt":"07:00","duration":7,"endsAt":"07:07","frequency":"once","startsOn":"2016-12-07","until":"","description":"Dia 7 de dezembro de 2016 \\u00e0s 07:00","price":"7"}}],"name":"Space 7","location":{"latitude":"-23.5394312","longitude":"-46.6915816"},"endereco":"Rua Engenheiro Francisco Azevedo, 216 , Jardim Vera Cruz, 05030-010, S&atilde;o Paulo, SP","revision":37},"8":{"items":[{"id":155,"description":"Dia 8 de dezembro de 2016 &agrave;s 08:00","_startsOn":null,"_endsOn":null,"_startsAt":null,"_endsAt":null,"frequency":"once","count":null,"_until":null,"rule":{"spaceId":"8","startsAt":"08:00","duration":8,"endsAt":"08:08","frequency":"once","startsOn":"2016-12-08","until":"","description":"Dia 8 de dezembro de 2016 \\u00e0s 08:00","price":"8"}}],"name":"Space 8","location":{"latitude":"-23.5466151","longitude":"-46.6468627"},"endereco":"Rua Rego Freitas, 530 , Rep&uacute;blica, 01220-010, S&atilde;o Paulo, SP","revision":38}}
514	2017-09-07 19:06:11	owner	{"id":357,"name":"New 2","shortDescription":"curta","revision":36}
515	2017-09-07 19:06:11	_agents	{"colegas":[{"id":5,"name":"Staff User 1","revision":28}],"group-admin":[{"id":7,"name":"Normal User 1","revision":34},{"id":8,"name":"Normal User 2","revision":35}]}
516	2017-09-07 19:06:11	occurrences	{"1":{"items":[{"id":162,"description":"Dia 21 de dezembro de 2016 &agrave;s 11:11","_startsOn":null,"_endsOn":null,"_startsAt":null,"_endsAt":null,"frequency":"once","count":null,"_until":null,"rule":{"spaceId":"1","startsAt":"11:11","duration":11,"endsAt":"11:22","frequency":"once","startsOn":"2016-12-21","until":"","description":"Dia 21 de dezembro de 2016 \\u00e0s 11:11","price":"gratuito"}}],"name":"Space 1","location":{"latitude":"-23.5443493","longitude":"-46.6444262"},"endereco":"Rua Ara&uacute;jo, 22 , Rep&uacute;blica, 01220-020, S&atilde;o Paulo, SP","revision":44},"2":{"items":[{"id":163,"description":"Dia 30 de dezembro de 2016 &agrave;s 22:22","_startsOn":null,"_endsOn":null,"_startsAt":null,"_endsAt":null,"frequency":"once","count":null,"_until":null,"rule":{"spaceId":"2","startsAt":"22:22","duration":22,"endsAt":"22:44","frequency":"once","startsOn":"2016-12-30","until":"","description":"Dia 30 de dezembro de 2016 \\u00e0s 22:22","price":"R$ 1,00"}}],"name":"Space 2","location":{"latitude":"-27.5906075","longitude":"-48.5129766"},"endereco":"Rua Tenente Jer&ocirc;nimo Borges, 33 , Santa M&ocirc;nica, 88035-050, Florian&oacute;polis, SC","revision":43}}
\.


--
-- Name: entity_revision_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('entity_revision_id_seq', 53, true);


--
-- Data for Name: entity_revision_revision_data; Type: TABLE DATA; Schema: public; Owner: -
--

COPY entity_revision_revision_data (revision_id, revision_data_id) FROM stdin;
1	1
1	2
1	3
1	4
1	5
1	6
1	7
1	8
1	9
1	10
1	11
1	12
1	13
1	14
1	15
2	16
2	17
2	18
2	19
2	20
2	21
2	22
2	23
2	24
2	25
2	26
2	27
2	28
2	29
3	30
3	31
3	32
3	33
3	34
3	35
3	36
3	37
3	38
3	39
3	40
3	41
3	42
3	43
3	44
4	45
4	46
4	47
4	48
4	49
4	50
4	51
4	52
4	53
4	54
4	55
4	56
4	57
4	58
5	59
5	60
5	61
5	62
5	63
5	64
5	65
5	66
5	67
5	68
5	69
5	70
5	71
5	72
5	73
5	74
5	75
5	76
5	77
5	78
5	79
5	80
5	81
5	82
6	83
6	84
6	85
6	86
6	87
6	88
6	89
6	90
6	91
6	92
6	93
6	94
6	95
6	96
6	97
6	98
6	99
6	100
6	101
6	102
6	103
7	104
7	105
7	106
7	107
7	108
7	109
7	110
7	111
7	112
7	113
7	114
7	115
7	116
7	117
7	118
7	119
7	120
7	121
7	122
7	123
7	124
7	125
8	126
8	127
8	128
8	129
8	130
8	131
8	132
8	133
8	134
8	135
8	136
8	137
8	138
8	139
9	140
9	141
9	142
9	143
9	144
9	145
9	146
9	147
9	148
9	149
9	150
9	151
9	152
9	153
9	154
9	155
9	156
9	157
9	158
10	159
10	160
10	161
10	162
10	163
10	164
10	165
10	166
10	167
10	168
10	169
10	170
10	171
10	172
10	173
10	174
10	175
10	176
10	177
10	178
10	179
11	180
11	181
11	182
11	183
11	184
11	185
11	186
11	187
11	188
11	189
11	190
11	191
11	192
11	193
11	194
11	195
11	196
11	197
11	198
11	199
11	200
11	201
12	202
12	203
12	204
12	205
12	206
12	207
12	208
12	209
12	210
12	211
12	212
12	213
12	214
12	215
12	216
12	217
12	218
12	219
12	220
12	221
12	222
12	223
13	224
13	225
13	226
13	227
13	228
13	229
13	230
13	231
13	232
13	233
13	234
13	235
13	236
13	237
13	238
13	239
13	240
13	241
13	242
13	243
13	244
13	245
14	246
14	247
14	248
14	249
14	250
14	251
14	252
14	253
14	254
14	255
14	256
14	257
14	258
14	259
14	260
14	261
14	262
14	263
14	264
14	265
14	266
14	267
15	268
15	269
15	270
15	271
15	272
15	273
15	274
15	275
15	276
15	277
15	278
15	279
15	280
15	281
15	282
15	283
15	284
15	285
15	286
15	287
15	288
15	289
16	290
16	291
16	292
16	293
16	294
16	295
16	296
16	297
16	298
16	299
16	300
16	301
16	302
16	303
16	304
16	305
16	306
16	307
16	308
16	309
16	310
16	311
16	312
16	313
17	314
17	315
17	316
17	317
17	318
17	319
17	320
17	321
17	322
17	323
17	324
17	325
17	326
17	327
17	328
17	329
17	330
17	331
17	332
17	333
17	334
17	335
18	336
18	337
18	338
18	339
18	340
18	341
18	342
18	343
18	344
18	345
18	346
18	347
18	348
18	349
18	350
18	351
18	352
18	353
18	354
18	355
18	356
19	357
19	358
19	359
19	360
19	361
19	362
19	363
19	364
19	365
19	366
19	367
19	368
20	369
20	370
20	371
20	372
20	373
20	374
20	375
20	376
20	377
20	378
20	379
21	380
21	381
21	382
21	383
21	384
21	385
21	386
21	387
21	388
21	389
21	390
21	391
22	392
22	393
22	394
22	395
22	396
22	397
22	398
22	399
22	400
22	401
22	402
23	403
23	404
23	405
23	406
23	407
23	408
23	409
23	410
23	411
23	412
23	413
24	414
24	415
24	416
24	417
24	418
24	419
24	420
24	421
24	422
24	423
24	424
24	425
24	426
24	427
24	428
25	429
25	430
25	431
25	432
25	433
25	434
25	435
25	436
25	437
25	438
25	439
25	440
25	441
25	442
25	443
26	444
26	445
26	446
26	447
26	448
26	449
26	450
26	451
26	452
26	453
26	454
26	455
26	456
26	457
26	458
26	459
26	460
27	461
27	462
27	463
27	464
27	465
27	466
27	467
27	468
27	469
27	470
27	471
27	472
27	473
27	474
27	475
28	1
28	2
28	3
28	4
28	5
28	6
28	7
28	8
28	9
28	10
28	11
28	476
28	477
28	14
28	15
29	16
29	17
29	18
29	19
29	20
29	21
29	22
29	23
29	24
29	25
29	26
29	478
29	479
29	29
30	30
30	31
30	32
30	33
30	34
30	35
30	36
30	37
30	38
30	39
30	40
30	480
30	481
30	43
30	44
31	45
31	46
31	47
31	48
31	49
31	50
31	51
31	52
31	53
31	54
31	55
31	482
31	483
31	58
32	59
32	60
32	61
32	62
32	63
32	64
32	65
32	66
32	67
32	68
32	69
32	70
32	71
32	72
32	73
32	74
32	75
32	76
32	77
32	78
32	484
32	485
32	81
32	82
33	83
33	84
33	85
33	86
33	87
33	88
33	89
33	90
33	91
33	92
33	93
33	94
33	95
33	96
33	97
33	98
33	99
33	100
33	486
33	487
33	103
34	104
34	105
34	106
34	107
34	108
34	109
34	110
34	111
34	112
34	113
34	114
34	115
34	116
34	117
34	118
34	119
34	120
34	121
34	488
34	489
34	124
34	125
35	126
35	127
35	128
35	129
35	130
35	131
35	132
35	133
35	134
35	135
35	136
35	490
35	491
35	139
36	159
36	160
36	161
36	162
36	163
36	164
36	165
36	166
36	167
36	168
36	169
36	170
36	171
36	172
36	173
36	174
36	175
36	176
36	492
36	178
36	179
37	180
37	181
37	182
37	183
37	184
37	185
37	186
37	187
37	188
37	189
37	190
37	493
37	192
37	193
37	194
37	195
37	196
37	197
37	198
37	199
37	200
37	201
38	202
38	203
38	204
38	205
38	206
38	207
38	208
38	209
38	210
38	211
38	212
38	494
38	214
38	215
38	216
38	217
38	218
38	219
38	220
38	221
38	222
38	223
39	224
39	225
39	226
39	227
39	228
39	229
39	230
39	231
39	232
39	233
39	234
39	495
39	236
39	237
39	238
39	239
39	240
39	241
39	242
39	243
39	244
39	245
40	246
40	247
40	248
40	249
40	250
40	251
40	252
40	253
40	254
40	255
40	256
40	496
40	258
40	259
40	260
40	261
40	262
40	263
40	264
40	265
40	266
40	267
41	268
41	269
41	270
41	271
41	272
41	273
41	274
41	275
41	276
41	277
41	278
41	497
41	280
41	281
41	282
41	283
41	284
41	285
41	286
41	287
41	288
41	289
42	290
42	291
42	292
42	293
42	294
42	295
42	296
42	297
42	298
42	299
42	300
42	498
42	302
42	303
42	304
42	305
42	306
42	307
42	308
42	309
42	310
42	311
42	312
42	313
43	314
43	315
43	316
43	317
43	318
43	319
43	320
43	321
43	322
43	323
43	324
43	499
43	326
43	327
43	328
43	329
43	330
43	331
43	332
43	333
43	334
43	335
44	336
44	337
44	338
44	339
44	340
44	341
44	342
44	343
44	344
44	345
44	346
44	500
44	348
44	349
44	350
44	351
44	352
44	353
44	354
44	355
44	356
45	357
45	358
45	359
45	360
45	361
45	362
45	363
45	364
45	365
45	501
45	367
45	502
46	369
46	370
46	371
46	372
46	373
46	374
46	375
46	376
46	377
46	503
46	379
47	380
47	381
47	382
47	383
47	384
47	385
47	386
47	387
47	388
47	504
47	390
47	391
48	392
48	393
48	394
48	395
48	396
48	397
48	398
48	399
48	400
48	505
48	402
49	403
49	404
49	405
49	406
49	407
49	408
49	409
49	410
49	411
49	506
49	413
50	414
50	415
50	416
50	417
50	418
50	419
50	420
50	421
50	422
50	507
50	424
50	425
50	426
50	427
50	508
51	429
51	430
51	431
51	432
51	433
51	434
51	435
51	436
51	437
51	509
51	439
51	440
51	441
51	442
51	510
52	444
52	445
52	446
52	447
52	448
52	449
52	450
52	451
52	452
52	511
52	454
52	455
52	456
52	457
52	512
52	513
52	460
53	461
53	462
53	463
53	464
53	465
53	466
53	467
53	468
53	469
53	514
53	471
53	472
53	473
53	515
53	516
\.


--
-- Data for Name: event; Type: TABLE DATA; Schema: public; Owner: -
--

COPY event (id, project_id, name, short_description, long_description, rules, create_timestamp, status, agent_id, is_verified, type, update_timestamp, subsite_id) FROM stdin;
6	\N	Event 6	of Staff User 2		\N	2014-05-21 18:04:44	1	6	f	1	2017-09-07 19:06:10	\N
8	\N	Event 8	of Normal User 1		\N	2014-05-21 18:04:44	1	8	f	1	2017-09-07 19:06:10	\N
1	\N	Event 1	of Super Admin 1		\N	2014-05-21 18:04:44	1	1	t	1	2017-09-07 19:06:10	\N
2	\N	Event 2	of Super Admin 2		\N	2014-05-21 18:04:44	1	2	f	1	2017-09-07 19:06:11	\N
4	\N	Event 4	of Admin 2		\N	2014-05-21 18:04:44	1	4	f	1	2017-09-07 19:06:11	\N
3	\N	Event 3	of Admin 1		\N	2014-05-21 18:04:44	1	3	t	1	2017-09-07 19:06:11	\N
5	\N	Event 5	of Staff User 1		\N	2014-05-21 18:04:44	1	5	t	1	2017-09-07 19:06:11	\N
7	3	Event 7	of Normal User 1		\N	2014-05-21 18:04:44	1	7	t	1	2017-09-07 19:06:11	\N
522	\N	Novo Evento	pequeno evento		\N	2016-12-15 23:56:29	1	357	f	1	2017-09-07 19:06:11	\N
\.


--
-- Name: event_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('event_id_seq', 522, true);


--
-- Data for Name: event_meta; Type: TABLE DATA; Schema: public; Owner: -
--

COPY event_meta (key, object_id, value, id) FROM stdin;
classificacaoEtaria             	1	Livre	1
classificacaoEtaria             	2	Livre	2
classificacaoEtaria             	3	Livre	3
classificacaoEtaria             	4	Livre	4
classificacaoEtaria             	5	Livre	5
classificacaoEtaria             	6	Livre	6
classificacaoEtaria             	7	Livre	7
classificacaoEtaria             	8	Livre	8
classificacaoEtaria	3	Livre	9
classificacaoEtaria	5	14 anos	10
classificacaoEtaria	7	16 anos	11
classificacaoEtaria	522	Livre	12
origin_site	522	mapas.rafa	13
\.


--
-- Name: event_meta_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('event_meta_id_seq', 13, true);


--
-- Data for Name: event_occurrence; Type: TABLE DATA; Schema: public; Owner: -
--

COPY event_occurrence (id, space_id, event_id, rule, starts_on, ends_on, starts_at, ends_at, frequency, separation, count, until, timezone_name, status) FROM stdin;
142	2	3	{"spaceId":"2","startsAt":"10:00","duration":30,"endsAt":"10:30","frequency":"daily","startsOn":"2016-12-01","until":"2016-12-30","description":"Diariamente de 1 a 30 de dezembro de 2016 \\u00e0s 10:00","price":"Gratuito"}	2016-12-01	\N	2016-12-01 10:00:00	2016-12-01 10:30:00	daily	1	\N	2016-12-30	Etc/UTC	1
143	1	3	{"spaceId":"1","startsAt":"15:00","duration":120,"endsAt":"17:00","frequency":"once","startsOn":"2016-12-16","until":"","description":"Dia 16 de dezembro de 2016 \\u00e0s 15:00","price":"Gratuito"}	2016-12-16	\N	2016-12-16 15:00:00	2016-12-16 17:00:00	once	1	\N	\N	Etc/UTC	1
144	6	5	{"spaceId":"6","startsAt":"08:00","duration":5,"endsAt":"08:05","frequency":"weekly","startsOn":"2016-11-01","until":"2017-01-31","day":{"1":"on","4":"on","6":"on"},"description":"Toda seg, qui e s\\u00e1b de 1 de novembro de 2016 a 31 de janeiro de 2017 \\u00e0s 08:00","price":"R$5,00"}	2016-11-01	\N	2016-11-01 08:00:00	2016-11-01 08:05:00	weekly	1	\N	2017-01-31	Etc/UTC	1
145	4	5	{"spaceId":"4","startsAt":"09:00","duration":15,"endsAt":"09:15","frequency":"weekly","startsOn":"2016-12-01","until":"2016-12-29","day":["on","on","on","on"],"description":"Todo dom, seg, ter e qua de 1 a 29 de dezembro de 2016 \\u00e0s 09:00","price":"R$90,00"}	2016-12-01	\N	2016-12-01 09:00:00	2016-12-01 09:15:00	weekly	1	\N	2016-12-29	Etc/UTC	1
146	1	6	{"spaceId":"1","startsAt":"11:11","duration":11,"endsAt":"11:22","frequency":"once","startsOn":"2016-12-14","until":"","description":"Dia 14 de dezembro de 2016 \\u00e0s 11:11","price":"33"}	2016-12-14	\N	2016-12-14 11:11:00	2016-12-14 11:22:00	once	1	\N	\N	Etc/UTC	1
147	1	6	{"spaceId":"1","startsAt":"13:00","duration":213,"endsAt":"16:33","frequency":"once","startsOn":"2016-12-21","until":"","description":"Dia 21 de dezembro de 2016 \\u00e0s 13:00","price":"R$5,00"}	2016-12-21	\N	2016-12-21 13:00:00	2016-12-21 16:33:00	once	1	\N	\N	Etc/UTC	1
148	1	7	{"spaceId":"1","startsAt":"01:00","duration":1,"endsAt":"01:01","frequency":"once","startsOn":"2016-12-01","until":"","description":"Dia 1 de dezembro de 2016 \\u00e0 01:00","price":"33"}	2016-12-01	\N	2016-12-01 01:00:00	2016-12-01 01:01:00	once	1	\N	\N	Etc/UTC	1
149	2	7	{"spaceId":"2","startsAt":"02:00","duration":2,"endsAt":"02:02","frequency":"once","startsOn":"2016-12-02","until":"","description":"Dia 2 de dezembro de 2016 \\u00e0s 02:00","price":"12"}	2016-12-02	\N	2016-12-02 02:00:00	2016-12-02 02:02:00	once	1	\N	\N	Etc/UTC	1
150	3	7	{"spaceId":"3","startsAt":"03:00","duration":3,"endsAt":"03:03","frequency":"once","startsOn":"2016-12-03","until":"","description":"Dia 3 de dezembro de 2016 \\u00e0s 03","price":"3"}	2016-12-03	\N	2016-12-03 03:00:00	2016-12-03 03:03:00	once	1	\N	\N	Etc/UTC	1
151	4	7	{"spaceId":"4","startsAt":"04:00","duration":4,"endsAt":"04:04","frequency":"once","startsOn":"2016-12-04","until":"","description":"Dia 4 de dezembro de 2016 \\u00e0s 04:00","price":"4"}	2016-12-04	\N	2016-12-04 04:00:00	2016-12-04 04:04:00	once	1	\N	\N	Etc/UTC	1
152	5	7	{"spaceId":"5","startsAt":"05:00","duration":5,"endsAt":"05:05","frequency":"once","startsOn":"2016-12-05","until":"","description":"Dia 5 de dezembro de 2016 \\u00e0s 05:00","price":"5"}	2016-12-05	\N	2016-12-05 05:00:00	2016-12-05 05:05:00	once	1	\N	\N	Etc/UTC	1
153	6	7	{"spaceId":"6","startsAt":"06:00","duration":6,"endsAt":"06:06","frequency":"once","startsOn":"2016-12-06","until":"","description":"Dia 6 de dezembro de 2016 \\u00e0s 06:00","price":"6"}	2016-12-06	\N	2016-12-06 06:00:00	2016-12-06 06:06:00	once	1	\N	\N	Etc/UTC	1
154	7	7	{"spaceId":"7","startsAt":"07:00","duration":7,"endsAt":"07:07","frequency":"once","startsOn":"2016-12-07","until":"","description":"Dia 7 de dezembro de 2016 \\u00e0s 07:00","price":"7"}	2016-12-07	\N	2016-12-07 07:00:00	2016-12-07 07:07:00	once	1	\N	\N	Etc/UTC	1
155	8	7	{"spaceId":"8","startsAt":"08:00","duration":8,"endsAt":"08:08","frequency":"once","startsOn":"2016-12-08","until":"","description":"Dia 8 de dezembro de 2016 \\u00e0s 08:00","price":"8"}	2016-12-08	\N	2016-12-08 08:00:00	2016-12-08 08:08:00	once	1	\N	\N	Etc/UTC	1
162	1	522	{"spaceId":"1","startsAt":"11:11","duration":11,"endsAt":"11:22","frequency":"once","startsOn":"2016-12-21","until":"","description":"Dia 21 de dezembro de 2016 \\u00e0s 11:11","price":"gratuito"}	2016-12-21	\N	2016-12-21 11:11:00	2016-12-21 11:22:00	once	1	\N	\N	Etc/UTC	-5
163	2	522	{"spaceId":"2","startsAt":"22:22","duration":22,"endsAt":"22:44","frequency":"once","startsOn":"2016-12-30","until":"","description":"Dia 30 de dezembro de 2016 \\u00e0s 22:22","price":"R$ 1,00"}	2016-12-30	\N	2016-12-30 22:22:00	2016-12-30 22:44:00	once	1	\N	\N	Etc/UTC	1
\.


--
-- Data for Name: event_occurrence_cancellation; Type: TABLE DATA; Schema: public; Owner: -
--

COPY event_occurrence_cancellation (id, event_occurrence_id, date) FROM stdin;
\.


--
-- Name: event_occurrence_cancellation_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('event_occurrence_cancellation_id_seq', 1, true);


--
-- Name: event_occurrence_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('event_occurrence_id_seq', 163, true);


--
-- Data for Name: event_occurrence_recurrence; Type: TABLE DATA; Schema: public; Owner: -
--

COPY event_occurrence_recurrence (id, event_occurrence_id, month, day, week) FROM stdin;
107	144	\N	1	\N
108	144	\N	4	\N
109	144	\N	6	\N
110	145	\N	0	\N
111	145	\N	1	\N
112	145	\N	2	\N
113	145	\N	3	\N
\.


--
-- Name: event_occurrence_recurrence_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('event_occurrence_recurrence_id_seq', 116, true);


--
-- Data for Name: file; Type: TABLE DATA; Schema: public; Owner: -
--

COPY file (id, md5, mime_type, name, object_type, object_id, create_timestamp, grp, description, parent_id, path) FROM stdin;
4	5dc784743746f51597fc5aca8d6fb5b7	image/jpeg	imagem4-570b654c331f46e4d3f3128fc55722d3.jpeg	MapasCulturais\\Entities\\Space	2	2016-12-15 14:12:31	img:avatarMedium	\N	2	space/2/file/2/imagem4-570b654c331f46e4d3f3128fc55722d3.jpeg
5	35bbe33b02e7e12beaea44ccb51ed97e	image/jpeg	imagem4-792da4142aad216c71edf68c0c3387ef.jpeg	MapasCulturais\\Entities\\Space	2	2016-12-15 14:12:31	img:avatarBig	\N	2	space/2/file/2/imagem4-792da4142aad216c71edf68c0c3387ef.jpeg
6	a6c571bb166e02a180208234d202708f	image/jpeg	imagem1.jpeg	MapasCulturais\\Entities\\Space	4	2016-12-15 14:12:45	avatar	\N	\N	space/4/imagem1.jpeg
8	2f630aa1bba07be1ad4abd9bd9cb460b	image/jpeg	imagem1-edddfc71895c873e781944d65e9fc3e0.jpeg	MapasCulturais\\Entities\\Space	4	2016-12-15 14:12:45	img:avatarMedium	\N	6	space/4/file/6/imagem1-edddfc71895c873e781944d65e9fc3e0.jpeg
9	c0c83d612112a51fdda383127a5e00ea	image/jpeg	imagem1-7a9bc5ba8184c729ca3ce9c850ee90b5.jpeg	MapasCulturais\\Entities\\Space	4	2016-12-15 14:12:45	img:avatarBig	\N	6	space/4/file/6/imagem1-7a9bc5ba8184c729ca3ce9c850ee90b5.jpeg
10	c380de3fb7ac84320357c6b0852d37d6	image/jpeg	imagem3.jpeg	MapasCulturais\\Entities\\Space	6	2016-12-15 14:18:24	avatar	\N	\N	space/6/imagem3.jpeg
11	e472306e5ae360b8f2880966fbeb1180	image/jpeg	imagem3-ed0f3136f28c0cbcea89572e0bf16825.jpeg	MapasCulturais\\Entities\\Space	6	2016-12-15 14:18:24	img:avatarSmall	\N	10	space/6/file/10/imagem3-ed0f3136f28c0cbcea89572e0bf16825.jpeg
13	c2c0986bbba641695972c9ca67eace56	image/jpeg	imagem3-1d54b3eea7eeb5139f95fbb00f0a0498.jpeg	MapasCulturais\\Entities\\Space	6	2016-12-15 14:18:24	img:avatarBig	\N	10	space/6/file/10/imagem3-1d54b3eea7eeb5139f95fbb00f0a0498.jpeg
14	02c3557636e47601e2c3bf97a1aacd34	image/jpeg	imagem2.jpeg	MapasCulturais\\Entities\\Space	1	2016-12-15 14:19:52	avatar	\N	\N	space/1/imagem2.jpeg
15	1eb521a246d2dfc7e2efc5e502643915	image/jpeg	imagem2-1d80d1d4d8de1e61294630edb215f103.jpeg	MapasCulturais\\Entities\\Space	1	2016-12-15 14:19:52	img:avatarSmall	\N	14	space/1/file/14/imagem2-1d80d1d4d8de1e61294630edb215f103.jpeg
17	9e12dc99411445974b4c9776bf262662	image/jpeg	imagem2-742ae267c66f6f4df1a34a11ebb3c618.jpeg	MapasCulturais\\Entities\\Space	1	2016-12-15 14:19:52	img:avatarBig	\N	14	space/1/file/14/imagem2-742ae267c66f6f4df1a34a11ebb3c618.jpeg
18	a6c571bb166e02a180208234d202708f	image/jpeg	imagem1.jpeg	MapasCulturais\\Entities\\Agent	3	2016-12-15 14:22:05	avatar	\N	\N	agent/3/imagem1.jpeg
19	0a94172721b6d02d216888d21c00e108	image/jpeg	imagem1-0108e4d03baa58da37295fac2878acaf.jpeg	MapasCulturais\\Entities\\Agent	3	2016-12-15 14:22:05	img:avatarSmall	\N	18	agent/3/file/18/imagem1-0108e4d03baa58da37295fac2878acaf.jpeg
20	2f630aa1bba07be1ad4abd9bd9cb460b	image/jpeg	imagem1-edddfc71895c873e781944d65e9fc3e0.jpeg	MapasCulturais\\Entities\\Agent	3	2016-12-15 14:22:05	img:avatarMedium	\N	18	agent/3/file/18/imagem1-edddfc71895c873e781944d65e9fc3e0.jpeg
22	02c3557636e47601e2c3bf97a1aacd34	image/jpeg	imagem2.jpeg	MapasCulturais\\Entities\\Agent	4	2016-12-15 14:24:36	avatar	\N	\N	agent/4/imagem2.jpeg
23	1eb521a246d2dfc7e2efc5e502643915	image/jpeg	imagem2-1d80d1d4d8de1e61294630edb215f103.jpeg	MapasCulturais\\Entities\\Agent	4	2016-12-15 14:24:36	img:avatarSmall	\N	22	agent/4/file/22/imagem2-1d80d1d4d8de1e61294630edb215f103.jpeg
24	c0d2bbe7a87f902e5889c6920dcb657f	image/jpeg	imagem2-6086f8a4969eda83f4fe75a5bd40603d.jpeg	MapasCulturais\\Entities\\Agent	4	2016-12-15 14:24:36	img:avatarMedium	\N	22	agent/4/file/22/imagem2-6086f8a4969eda83f4fe75a5bd40603d.jpeg
26	3f33b6a80a91f393ac2b023b5511d373	image/jpeg	imagem4.jpeg	MapasCulturais\\Entities\\Agent	7	2016-12-15 14:33:03	avatar	\N	\N	agent/7/imagem4.jpeg
27	41d23133056bf227bae7ab1c89c863df	image/jpeg	imagem4-4e0f09ec602df7f0dbe0ffa44c19eb5e.jpeg	MapasCulturais\\Entities\\Agent	7	2016-12-15 14:33:03	img:avatarSmall	\N	26	agent/7/file/26/imagem4-4e0f09ec602df7f0dbe0ffa44c19eb5e.jpeg
28	5dc784743746f51597fc5aca8d6fb5b7	image/jpeg	imagem4-570b654c331f46e4d3f3128fc55722d3.jpeg	MapasCulturais\\Entities\\Agent	7	2016-12-15 14:33:03	img:avatarMedium	\N	26	agent/7/file/26/imagem4-570b654c331f46e4d3f3128fc55722d3.jpeg
30	c380de3fb7ac84320357c6b0852d37d6	image/jpeg	imagem3.jpeg	MapasCulturais\\Entities\\Agent	8	2016-12-15 14:33:48	avatar	\N	\N	agent/8/imagem3.jpeg
31	e472306e5ae360b8f2880966fbeb1180	image/jpeg	imagem3-ed0f3136f28c0cbcea89572e0bf16825.jpeg	MapasCulturais\\Entities\\Agent	8	2016-12-15 14:33:48	img:avatarSmall	\N	30	agent/8/file/30/imagem3-ed0f3136f28c0cbcea89572e0bf16825.jpeg
32	2256bbbfb2595ec8d78ae4a9e22ba0d4	image/jpeg	imagem3-8e0c7f12f218eec531833cebb8deee4e.jpeg	MapasCulturais\\Entities\\Agent	8	2016-12-15 14:33:48	img:avatarMedium	\N	30	agent/8/file/30/imagem3-8e0c7f12f218eec531833cebb8deee4e.jpeg
33	c2c0986bbba641695972c9ca67eace56	image/jpeg	imagem3-1d54b3eea7eeb5139f95fbb00f0a0498.jpeg	MapasCulturais\\Entities\\Agent	8	2016-12-15 14:33:48	img:avatarBig	\N	30	agent/8/file/30/imagem3-1d54b3eea7eeb5139f95fbb00f0a0498.jpeg
34	a6c571bb166e02a180208234d202708f	image/jpeg	imagem1.jpeg	MapasCulturais\\Entities\\Event	5	2016-12-15 14:37:19	avatar	\N	\N	event/5/imagem1.jpeg
36	2f630aa1bba07be1ad4abd9bd9cb460b	image/jpeg	imagem1-edddfc71895c873e781944d65e9fc3e0.jpeg	MapasCulturais\\Entities\\Event	5	2016-12-15 14:37:19	img:avatarMedium	\N	34	event/5/file/34/imagem1-edddfc71895c873e781944d65e9fc3e0.jpeg
37	c0c83d612112a51fdda383127a5e00ea	image/jpeg	imagem1-7a9bc5ba8184c729ca3ce9c850ee90b5.jpeg	MapasCulturais\\Entities\\Event	5	2016-12-15 14:37:19	img:avatarBig	\N	34	event/5/file/34/imagem1-7a9bc5ba8184c729ca3ce9c850ee90b5.jpeg
38	c380de3fb7ac84320357c6b0852d37d6	image/jpeg	imagem3.jpeg	MapasCulturais\\Entities\\Event	6	2016-12-15 14:39:58	avatar	\N	\N	event/6/imagem3.jpeg
40	2256bbbfb2595ec8d78ae4a9e22ba0d4	image/jpeg	imagem3-8e0c7f12f218eec531833cebb8deee4e.jpeg	MapasCulturais\\Entities\\Event	6	2016-12-15 14:39:58	img:avatarMedium	\N	38	event/6/file/38/imagem3-8e0c7f12f218eec531833cebb8deee4e.jpeg
41	c2c0986bbba641695972c9ca67eace56	image/jpeg	imagem3-1d54b3eea7eeb5139f95fbb00f0a0498.jpeg	MapasCulturais\\Entities\\Event	6	2016-12-15 14:39:58	img:avatarBig	\N	38	event/6/file/38/imagem3-1d54b3eea7eeb5139f95fbb00f0a0498.jpeg
42	02c3557636e47601e2c3bf97a1aacd34	image/jpeg	imagem2.jpeg	MapasCulturais\\Entities\\Event	7	2016-12-15 14:42:32	avatar	\N	\N	event/7/imagem2.jpeg
44	c0d2bbe7a87f902e5889c6920dcb657f	image/jpeg	imagem2-6086f8a4969eda83f4fe75a5bd40603d.jpeg	MapasCulturais\\Entities\\Event	7	2016-12-15 14:42:32	img:avatarMedium	\N	42	event/7/file/42/imagem2-6086f8a4969eda83f4fe75a5bd40603d.jpeg
45	9e12dc99411445974b4c9776bf262662	image/jpeg	imagem2-742ae267c66f6f4df1a34a11ebb3c618.jpeg	MapasCulturais\\Entities\\Event	7	2016-12-15 14:42:32	img:avatarBig	\N	42	event/7/file/42/imagem2-742ae267c66f6f4df1a34a11ebb3c618.jpeg
2	3f33b6a80a91f393ac2b023b5511d373	image/jpeg	imagem4.jpeg	MapasCulturais\\Entities\\Space	2	2016-12-15 14:12:31	avatar	\N	\N	space/2/imagem4.jpeg
3	41d23133056bf227bae7ab1c89c863df	image/jpeg	imagem4-4e0f09ec602df7f0dbe0ffa44c19eb5e.jpeg	MapasCulturais\\Entities\\Space	2	2016-12-15 14:12:31	img:avatarSmall	\N	2	space/2/file/2/imagem4-4e0f09ec602df7f0dbe0ffa44c19eb5e.jpeg
7	0a94172721b6d02d216888d21c00e108	image/jpeg	imagem1-0108e4d03baa58da37295fac2878acaf.jpeg	MapasCulturais\\Entities\\Space	4	2016-12-15 14:12:45	img:avatarSmall	\N	6	space/4/file/6/imagem1-0108e4d03baa58da37295fac2878acaf.jpeg
12	2256bbbfb2595ec8d78ae4a9e22ba0d4	image/jpeg	imagem3-8e0c7f12f218eec531833cebb8deee4e.jpeg	MapasCulturais\\Entities\\Space	6	2016-12-15 14:18:24	img:avatarMedium	\N	10	space/6/file/10/imagem3-8e0c7f12f218eec531833cebb8deee4e.jpeg
16	c0d2bbe7a87f902e5889c6920dcb657f	image/jpeg	imagem2-6086f8a4969eda83f4fe75a5bd40603d.jpeg	MapasCulturais\\Entities\\Space	1	2016-12-15 14:19:52	img:avatarMedium	\N	14	space/1/file/14/imagem2-6086f8a4969eda83f4fe75a5bd40603d.jpeg
21	c0c83d612112a51fdda383127a5e00ea	image/jpeg	imagem1-7a9bc5ba8184c729ca3ce9c850ee90b5.jpeg	MapasCulturais\\Entities\\Agent	3	2016-12-15 14:22:05	img:avatarBig	\N	18	agent/3/file/18/imagem1-7a9bc5ba8184c729ca3ce9c850ee90b5.jpeg
25	9e12dc99411445974b4c9776bf262662	image/jpeg	imagem2-742ae267c66f6f4df1a34a11ebb3c618.jpeg	MapasCulturais\\Entities\\Agent	4	2016-12-15 14:24:36	img:avatarBig	\N	22	agent/4/file/22/imagem2-742ae267c66f6f4df1a34a11ebb3c618.jpeg
29	35bbe33b02e7e12beaea44ccb51ed97e	image/jpeg	imagem4-792da4142aad216c71edf68c0c3387ef.jpeg	MapasCulturais\\Entities\\Agent	7	2016-12-15 14:33:03	img:avatarBig	\N	26	agent/7/file/26/imagem4-792da4142aad216c71edf68c0c3387ef.jpeg
35	0a94172721b6d02d216888d21c00e108	image/jpeg	imagem1-0108e4d03baa58da37295fac2878acaf.jpeg	MapasCulturais\\Entities\\Event	5	2016-12-15 14:37:19	img:avatarSmall	\N	34	event/5/file/34/imagem1-0108e4d03baa58da37295fac2878acaf.jpeg
39	e472306e5ae360b8f2880966fbeb1180	image/jpeg	imagem3-ed0f3136f28c0cbcea89572e0bf16825.jpeg	MapasCulturais\\Entities\\Event	6	2016-12-15 14:39:58	img:avatarSmall	\N	38	event/6/file/38/imagem3-ed0f3136f28c0cbcea89572e0bf16825.jpeg
43	1eb521a246d2dfc7e2efc5e502643915	image/jpeg	imagem2-1d80d1d4d8de1e61294630edb215f103.jpeg	MapasCulturais\\Entities\\Event	7	2016-12-15 14:42:32	img:avatarSmall	\N	42	event/7/file/42/imagem2-1d80d1d4d8de1e61294630edb215f103.jpeg
46	10305e262127a496bdf7fceefa0ec85f	image/jpeg	imagem1-220f3fdda72e0ff473c8d3488d0fec7d.jpeg	MapasCulturais\\Entities\\Event	5	2016-12-15 14:54:48	img:galleryFull	\N	34	event/5/file/34/imagem1-220f3fdda72e0ff473c8d3488d0fec7d.jpeg
\.


--
-- Name: file_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('file_id_seq', 74, true);


--
-- Data for Name: geo_division; Type: TABLE DATA; Schema: public; Owner: -
--

COPY geo_division (id, parent_id, type, cod, name, geom) FROM stdin;
\.


--
-- Name: geo_division_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('geo_division_id_seq', 1, false);


--
-- Data for Name: metadata; Type: TABLE DATA; Schema: public; Owner: -
--

COPY metadata (object_id, object_type, key, value) FROM stdin;
\.


--
-- Data for Name: metalist; Type: TABLE DATA; Schema: public; Owner: -
--

COPY metalist (id, object_type, object_id, grp, title, description, value, create_timestamp, "order") FROM stdin;
\.


--
-- Name: metalist_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('metalist_id_seq', 1, true);


--
-- Data for Name: notification; Type: TABLE DATA; Schema: public; Owner: -
--

COPY notification (id, user_id, request_id, message, create_timestamp, action_timestamp, status) FROM stdin;
1	3	\N	Seu último acesso foi em <b>21/05/2014</b>, atualize suas informações se necessário.	2016-12-15 11:57:24	\N	1
4	7	\N	Seu último acesso foi em <b>21/05/2014</b>, atualize suas informações se necessário.	2016-12-15 22:24:28	\N	1
7	11	\N	<a href="http://localhost:8888/agente/356/">New 1</a> aceitou o relacionamento do agente <a href="http://localhost:8888/agente/356/">New 1</a> com o agente <a href="http://localhost:8888/agente/357/">New 2</a>.	2016-12-15 23:53:41	\N	1
8	11	18	Sua requisição para criar a ocorrência do evento <a href="http://localhost:8888/evento/522/">Novo Evento</a> no espaço <a href="http://localhost:8888/espaco/1/">Space 1</a> foi enviada.	2016-12-15 23:57:14	\N	1
9	1	18	<a href="http://localhost:8888/agente/357/">New 2</a> quer adicionar o evento <a href="http://localhost:8888/evento/522/">Novo Evento</a> que ocorre <em>Dia 21 de dezembro de 2016 às 11:11</em> no espaço <a href="http://localhost:8888/espaco/1/">Space 1</a>.	2016-12-15 23:57:14	\N	1
12	11	20	Sua requisição para relacionar o agente <a href="http://localhost:8888/agente/8/">Normal User 2</a> ao evento <a href="http://localhost:8888/evento/522/">Novo Evento</a> foi enviada.	2016-12-15 23:57:42	\N	1
13	8	20	<a href="http://localhost:8888/agente/357/">New 2</a> quer relacionar o agente <a href="http://localhost:8888/agente/8/">Normal User 2</a> ao evento <a href="http://localhost:8888/evento/522/">Novo Evento</a>.	2016-12-15 23:57:42	\N	1
14	11	21	Sua requisição para relacionar o agente <a href="http://localhost:8888/agente/5/">Staff User 1</a> ao evento <a href="http://localhost:8888/evento/522/">Novo Evento</a> foi enviada.	2016-12-15 23:58:05	\N	1
15	5	21	<a href="http://localhost:8888/agente/357/">New 2</a> quer relacionar o agente <a href="http://localhost:8888/agente/5/">Staff User 1</a> ao evento <a href="http://localhost:8888/evento/522/">Novo Evento</a>.	2016-12-15 23:58:05	\N	1
16	11	\N	<a href="http://localhost:8888/agente/7/">Normal User 1</a> aceitou o relacionamento do agente <a href="http://localhost:8888/agente/7/">Normal User 1</a> com o evento <a href="http://localhost:8888/evento/522/">Novo Evento</a>.	2016-12-15 23:59:50	\N	1
17	5	\N	Seu último acesso foi em <b>21/05/2014</b>, atualize suas informações se necessário.	2016-12-15 23:59:59	\N	1
18	5	\N	O agente <b>Staff User 1</b> não é atualizado desde de <b>21/05/2014</b>, atualize as informações se necessário.<a class="btn btn-small btn-primary" href="http://localhost:8888/agentes/edita/5/">editar</a>	2016-12-15 23:59:59	\N	1
19	6	\N	Seu último acesso foi em <b>21/05/2014</b>, atualize suas informações se necessário.	2016-12-16 00:00:15	\N	1
20	6	\N	O agente <b>Staff User 2</b> não é atualizado desde de <b>21/05/2014</b>, atualize as informações se necessário.<a class="btn btn-small btn-primary" href="http://localhost:8888/agentes/edita/6/">editar</a>	2016-12-16 00:00:15	\N	1
23	2	\N	Seu último acesso foi em <b>21/05/2014</b>, atualize suas informações se necessário.	2016-12-16 00:01:45	\N	1
24	2	\N	O agente <b>Super Admin 2</b> não é atualizado desde de <b>21/05/2014</b>, atualize as informações se necessário.<a class="btn btn-small btn-primary" href="http://localhost:8888/agentes/edita/2/">editar</a>	2016-12-16 00:01:45	\N	1
25	11	\N	<a href="http://localhost:8888/agente/2/">Super Admin 2</a> aceitou adicionar o evento <a href="http://localhost:8888/evento/522/">Novo Evento</a> que ocorre <em>Dia 30 de dezembro de 2016 às 22:22</em> no espaço <a href="http://localhost:8888/espaco/2/">Space 2</a>.	2016-12-16 00:02:14	\N	1
26	4	\N	Seu último acesso foi em <b>21/05/2014</b>, atualize suas informações se necessário.	2016-12-16 00:07:54	\N	1
27	1	\N	Seu último acesso foi em <b>21/05/2014</b>, atualize suas informações se necessário.	2016-12-16 00:17:56	\N	1
28	1	\N	O agente <b>Super Admin 1</b> não é atualizado desde de <b>21/05/2014</b>, atualize as informações se necessário.<a class="btn btn-small btn-primary" href="http://localhost:8888/agentes/edita/1/">editar</a>	2016-12-16 00:17:56	\N	1
29	8	\N	Seu último acesso foi em <b>21/05/2014</b>, atualize suas informações se necessário.	2016-12-16 00:19:59	\N	1
\.


--
-- Name: notification_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('notification_id_seq', 29, true);


--
-- Data for Name: notification_meta; Type: TABLE DATA; Schema: public; Owner: -
--

COPY notification_meta (id, object_id, key, value) FROM stdin;
\.


--
-- Name: notification_meta_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('notification_meta_id_seq', 1, false);


--
-- Name: occurrence_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('occurrence_id_seq', 100000, false);


--
-- Data for Name: pcache; Type: TABLE DATA; Schema: public; Owner: -
--

COPY pcache (id, user_id, action, create_timestamp, object_type, object_id) FROM stdin;
1	5	@control	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	5
2	5	create	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	5
3	5	remove	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	5
4	5	destroy	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	5
5	5	changeOwner	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	5
6	5	view	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	5
7	5	modify	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	5
8	5	viewPrivateData	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	5
9	5	createAgentRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	5
10	5	createAgentRelationWithControl	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	5
11	5	removeAgentRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	5
12	5	removeAgentRelationWithControl	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	5
13	5	createSealRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	5
14	5	removeSealRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	5
15	6	@control	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	6
16	6	create	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	6
17	6	remove	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	6
18	6	destroy	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	6
19	6	changeOwner	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	6
20	6	view	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	6
21	6	modify	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	6
22	6	viewPrivateData	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	6
23	6	createAgentRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	6
24	6	createAgentRelationWithControl	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	6
25	6	removeAgentRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	6
26	6	removeAgentRelationWithControl	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	6
27	6	createSealRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	6
28	6	removeSealRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	6
29	7	@control	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	7
30	7	create	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	7
31	7	remove	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	7
32	7	destroy	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	7
33	7	changeOwner	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	7
34	7	view	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	7
35	7	modify	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	7
36	7	viewPrivateData	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	7
37	7	createAgentRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	7
38	7	createAgentRelationWithControl	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	7
39	7	removeAgentRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	7
40	7	removeAgentRelationWithControl	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	7
41	7	createSealRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	7
42	7	removeSealRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	7
43	8	@control	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	8
44	8	create	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	8
45	8	remove	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	8
46	8	destroy	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	8
47	8	changeOwner	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	8
48	8	view	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	8
49	8	modify	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	8
50	8	viewPrivateData	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	8
51	8	createAgentRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	8
52	8	createAgentRelationWithControl	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	8
53	8	removeAgentRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	8
54	8	removeAgentRelationWithControl	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	8
55	8	createSealRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	8
56	8	removeSealRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	8
57	10	@control	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	356
58	10	create	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	356
59	10	remove	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	356
60	10	destroy	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	356
61	10	changeOwner	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	356
62	10	view	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	356
63	10	modify	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	356
64	10	viewPrivateData	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	356
65	10	createAgentRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	356
66	10	createAgentRelationWithControl	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	356
67	10	removeAgentRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	356
68	10	removeAgentRelationWithControl	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	356
69	10	createSealRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	356
70	10	removeSealRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	356
71	11	@control	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	357
72	11	create	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	357
73	11	remove	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	357
74	11	destroy	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	357
75	11	changeOwner	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	357
76	11	view	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	357
77	11	modify	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	357
78	11	viewPrivateData	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	357
79	11	createAgentRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	357
80	11	createAgentRelationWithControl	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	357
81	11	removeAgentRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	357
82	11	removeAgentRelationWithControl	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	357
83	11	createSealRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	357
84	11	removeSealRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	357
85	10	@control	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	357
86	10	create	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	357
87	10	remove	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	357
88	10	destroy	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	357
89	10	changeOwner	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	357
90	10	view	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	357
91	10	modify	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	357
92	10	viewPrivateData	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	357
93	10	createAgentRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	357
94	10	createAgentRelationWithControl	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	357
95	10	removeAgentRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	357
96	10	removeAgentRelationWithControl	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	357
97	10	createSealRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	357
98	10	removeSealRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Agent	357
99	7	@control	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	7
100	7	view	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	7
101	7	modify	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	7
102	7	remove	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	7
103	7	changeOwner	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	7
104	7	create	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	7
105	7	viewPrivateData	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	7
106	7	createAgentRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	7
107	7	createAgentRelationWithControl	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	7
108	7	removeAgentRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	7
109	7	removeAgentRelationWithControl	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	7
110	7	createSealRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	7
111	7	removeSealRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	7
112	7	destroy	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	7
113	8	@control	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	8
114	8	view	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	8
115	8	modify	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	8
116	8	remove	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	8
117	8	changeOwner	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	8
118	8	create	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	8
119	8	viewPrivateData	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	8
120	8	createAgentRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	8
121	8	createAgentRelationWithControl	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	8
122	8	removeAgentRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	8
123	8	removeAgentRelationWithControl	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	8
124	8	createSealRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	8
125	8	removeSealRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	8
126	8	destroy	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	8
127	6	@control	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	6
128	6	view	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	6
129	6	modify	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	6
130	6	remove	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	6
131	6	changeOwner	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	6
132	6	create	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	6
133	6	viewPrivateData	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	6
134	6	createAgentRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	6
135	6	createAgentRelationWithControl	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	6
136	6	removeAgentRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	6
137	6	removeAgentRelationWithControl	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	6
138	6	createSealRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	6
139	6	removeSealRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	6
140	6	destroy	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	6
141	5	@control	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	5
142	5	view	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	5
143	5	modify	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	5
144	5	remove	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	5
145	5	changeOwner	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	5
146	5	create	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	5
147	5	viewPrivateData	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	5
148	5	createAgentRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	5
149	5	createAgentRelationWithControl	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	5
150	5	removeAgentRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	5
151	5	removeAgentRelationWithControl	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	5
152	5	createSealRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	5
153	5	removeSealRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	5
154	5	destroy	2017-09-07 16:06:08	MapasCulturais\\Entities\\Space	5
155	5	@control	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	5
156	5	createEvents	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	5
157	5	requestEventRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	5
158	5	modifyRegistrationFields	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	5
159	5	publishRegistrations	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	5
160	5	register	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	5
161	5	view	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	5
162	5	modify	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	5
163	5	remove	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	5
164	5	changeOwner	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	5
165	5	create	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	5
166	5	viewPrivateData	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	5
167	5	createAgentRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	5
168	5	createAgentRelationWithControl	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	5
169	5	removeAgentRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	5
170	5	removeAgentRelationWithControl	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	5
171	5	createSealRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	5
172	5	removeSealRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	5
173	5	destroy	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	5
174	6	@control	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	6
175	6	createEvents	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	6
176	6	requestEventRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	6
177	6	modifyRegistrationFields	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	6
178	6	publishRegistrations	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	6
179	6	register	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	6
180	6	view	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	6
181	6	modify	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	6
182	6	remove	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	6
183	6	changeOwner	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	6
184	6	create	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	6
185	6	viewPrivateData	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	6
186	6	createAgentRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	6
187	6	createAgentRelationWithControl	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	6
188	6	removeAgentRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	6
189	6	removeAgentRelationWithControl	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	6
190	6	createSealRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	6
191	6	removeSealRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	6
192	6	destroy	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	6
193	7	@control	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	7
194	7	createEvents	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	7
195	7	requestEventRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	7
196	7	modifyRegistrationFields	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	7
197	7	publishRegistrations	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	7
198	7	register	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	7
199	7	view	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	7
200	7	modify	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	7
201	7	remove	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	7
202	7	changeOwner	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	7
203	7	create	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	7
204	7	viewPrivateData	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	7
205	7	createAgentRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	7
206	7	createAgentRelationWithControl	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	7
207	7	removeAgentRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	7
208	7	removeAgentRelationWithControl	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	7
209	7	createSealRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	7
210	7	removeSealRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	7
211	7	destroy	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	7
212	8	@control	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	8
213	8	createEvents	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	8
214	8	requestEventRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	8
215	8	modifyRegistrationFields	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	8
216	8	publishRegistrations	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	8
217	8	register	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	8
218	8	view	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	8
219	8	modify	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	8
220	8	remove	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	8
221	8	changeOwner	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	8
222	8	create	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	8
223	8	viewPrivateData	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	8
224	8	createAgentRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	8
225	8	createAgentRelationWithControl	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	8
226	8	removeAgentRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	8
227	8	removeAgentRelationWithControl	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	8
228	8	createSealRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	8
229	8	removeSealRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	8
230	8	destroy	2017-09-07 16:06:08	MapasCulturais\\Entities\\Project	8
231	6	@control	2017-09-07 16:06:08	MapasCulturais\\Entities\\Event	6
232	6	create	2017-09-07 16:06:08	MapasCulturais\\Entities\\Event	6
233	6	modify	2017-09-07 16:06:08	MapasCulturais\\Entities\\Event	6
234	6	unpublish	2017-09-07 16:06:08	MapasCulturais\\Entities\\Event	6
235	6	publish	2017-09-07 16:06:08	MapasCulturais\\Entities\\Event	6
236	6	view	2017-09-07 16:06:08	MapasCulturais\\Entities\\Event	6
237	6	remove	2017-09-07 16:06:08	MapasCulturais\\Entities\\Event	6
238	6	changeOwner	2017-09-07 16:06:08	MapasCulturais\\Entities\\Event	6
239	6	viewPrivateData	2017-09-07 16:06:08	MapasCulturais\\Entities\\Event	6
240	6	createAgentRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Event	6
241	6	createAgentRelationWithControl	2017-09-07 16:06:08	MapasCulturais\\Entities\\Event	6
242	6	removeAgentRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Event	6
243	6	removeAgentRelationWithControl	2017-09-07 16:06:08	MapasCulturais\\Entities\\Event	6
244	6	createSealRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Event	6
245	6	removeSealRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Event	6
246	6	destroy	2017-09-07 16:06:08	MapasCulturais\\Entities\\Event	6
247	8	@control	2017-09-07 16:06:08	MapasCulturais\\Entities\\Event	8
248	8	create	2017-09-07 16:06:08	MapasCulturais\\Entities\\Event	8
249	8	modify	2017-09-07 16:06:08	MapasCulturais\\Entities\\Event	8
250	8	unpublish	2017-09-07 16:06:08	MapasCulturais\\Entities\\Event	8
251	8	publish	2017-09-07 16:06:08	MapasCulturais\\Entities\\Event	8
252	8	view	2017-09-07 16:06:08	MapasCulturais\\Entities\\Event	8
253	8	remove	2017-09-07 16:06:08	MapasCulturais\\Entities\\Event	8
254	8	changeOwner	2017-09-07 16:06:08	MapasCulturais\\Entities\\Event	8
255	8	viewPrivateData	2017-09-07 16:06:08	MapasCulturais\\Entities\\Event	8
256	8	createAgentRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Event	8
257	8	createAgentRelationWithControl	2017-09-07 16:06:08	MapasCulturais\\Entities\\Event	8
258	8	removeAgentRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Event	8
259	8	removeAgentRelationWithControl	2017-09-07 16:06:08	MapasCulturais\\Entities\\Event	8
260	8	createSealRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Event	8
261	8	removeSealRelation	2017-09-07 16:06:08	MapasCulturais\\Entities\\Event	8
262	8	destroy	2017-09-07 16:06:08	MapasCulturais\\Entities\\Event	8
263	5	@control	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	5
264	5	create	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	5
265	5	modify	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	5
266	5	unpublish	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	5
267	5	publish	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	5
268	5	view	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	5
269	5	remove	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	5
270	5	changeOwner	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	5
271	5	viewPrivateData	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	5
272	5	createAgentRelation	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	5
273	5	createAgentRelationWithControl	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	5
274	5	removeAgentRelation	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	5
275	5	removeAgentRelationWithControl	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	5
276	5	createSealRelation	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	5
277	5	removeSealRelation	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	5
278	5	destroy	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	5
279	7	@control	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	7
280	7	create	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	7
281	7	modify	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	7
282	7	unpublish	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	7
283	7	publish	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	7
284	7	view	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	7
285	7	remove	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	7
286	7	changeOwner	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	7
287	7	viewPrivateData	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	7
288	7	createAgentRelation	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	7
289	7	createAgentRelationWithControl	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	7
290	7	removeAgentRelation	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	7
291	7	removeAgentRelationWithControl	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	7
292	7	createSealRelation	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	7
293	7	removeSealRelation	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	7
294	7	destroy	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	7
295	11	@control	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	522
296	11	create	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	522
297	11	modify	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	522
298	11	unpublish	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	522
299	11	publish	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	522
300	11	view	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	522
301	11	remove	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	522
302	11	changeOwner	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	522
303	11	viewPrivateData	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	522
304	11	createAgentRelation	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	522
305	11	createAgentRelationWithControl	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	522
306	11	removeAgentRelation	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	522
307	11	removeAgentRelationWithControl	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	522
308	11	createSealRelation	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	522
309	11	removeSealRelation	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	522
310	11	destroy	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	522
311	10	@control	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	522
312	10	create	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	522
313	10	modify	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	522
314	10	unpublish	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	522
315	10	publish	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	522
316	10	view	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	522
317	10	remove	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	522
318	10	changeOwner	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	522
319	10	viewPrivateData	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	522
320	10	createAgentRelation	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	522
321	10	createAgentRelationWithControl	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	522
322	10	removeAgentRelation	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	522
323	10	removeAgentRelationWithControl	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	522
324	10	createSealRelation	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	522
325	10	removeSealRelation	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	522
326	10	destroy	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	522
327	7	@control	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	522
328	7	create	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	522
329	7	modify	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	522
330	7	unpublish	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	522
331	7	publish	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	522
332	7	view	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	522
333	7	remove	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	522
334	7	changeOwner	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	522
335	7	viewPrivateData	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	522
336	7	createAgentRelation	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	522
337	7	createAgentRelationWithControl	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	522
338	7	removeAgentRelation	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	522
339	7	removeAgentRelationWithControl	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	522
340	7	createSealRelation	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	522
341	7	removeSealRelation	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	522
342	7	destroy	2017-09-07 16:06:09	MapasCulturais\\Entities\\Event	522
343	7	@control	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	4
344	7	create	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	4
345	7	view	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	4
346	7	modify	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	4
347	7	remove	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	4
348	11	@control	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	7
349	11	create	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	7
350	11	view	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	7
351	11	modify	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	7
352	11	remove	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	7
353	11	@control	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	8
354	11	create	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	8
355	11	view	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	8
356	11	modify	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	8
357	11	remove	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	8
358	11	@control	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	12
359	11	create	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	12
360	11	view	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	12
361	11	modify	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	12
362	11	remove	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	12
363	8	@control	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	13
364	8	create	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	13
365	8	view	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	13
366	8	modify	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	13
367	8	remove	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	13
368	11	@control	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	14
369	11	create	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	14
370	11	view	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	14
371	11	modify	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	14
372	11	remove	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	14
373	5	@control	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	15
374	5	create	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	15
375	5	view	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	15
376	5	modify	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	15
377	5	remove	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	15
378	11	@control	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	16
379	11	create	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	16
380	11	view	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	16
381	11	modify	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	16
382	11	remove	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	16
383	5	@control	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	17
384	5	create	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	17
385	5	view	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	17
386	5	modify	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	17
387	5	remove	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	17
388	5	@control	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	18
389	5	create	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	18
390	5	view	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	18
391	5	modify	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	18
392	5	remove	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	18
393	6	@control	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	19
394	6	create	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	19
395	6	view	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	19
396	6	modify	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	19
397	6	remove	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	19
398	6	@control	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	20
399	6	create	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	20
400	6	view	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	20
401	6	modify	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	20
402	6	remove	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	20
403	11	@control	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	25
404	11	create	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	25
405	11	view	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	25
406	11	modify	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	25
407	11	remove	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	25
408	8	@control	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	29
409	8	create	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	29
410	8	view	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	29
411	8	modify	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	29
412	8	remove	2017-09-07 16:06:09	MapasCulturais\\Entities\\Notification	29
413	11	@control	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	18
414	11	create	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	18
415	11	approve	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	18
416	11	reject	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	18
417	11	view	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	18
418	11	modify	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	18
419	11	remove	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	18
420	10	@control	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	18
421	10	create	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	18
422	10	approve	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	18
423	10	reject	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	18
424	10	view	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	18
425	10	modify	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	18
426	10	remove	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	18
427	7	@control	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	18
428	7	create	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	18
429	7	approve	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	18
430	7	reject	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	18
431	7	view	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	18
432	7	modify	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	18
433	7	remove	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	18
434	11	@control	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	20
435	11	create	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	20
436	11	approve	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	20
437	11	reject	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	20
438	11	view	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	20
439	11	modify	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	20
440	11	remove	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	20
441	10	@control	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	20
442	10	create	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	20
443	10	approve	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	20
444	10	reject	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	20
445	10	view	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	20
446	10	modify	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	20
447	10	remove	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	20
448	7	@control	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	20
449	7	create	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	20
450	7	approve	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	20
451	7	reject	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	20
452	7	view	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	20
453	7	modify	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	20
454	7	remove	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	20
455	8	@control	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	20
456	8	create	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	20
457	8	approve	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	20
458	8	reject	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	20
459	8	view	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	20
460	8	modify	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	20
461	8	remove	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	20
462	11	@control	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	21
463	11	create	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	21
464	11	approve	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	21
465	11	reject	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	21
466	11	view	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	21
467	11	modify	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	21
468	11	remove	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	21
469	10	@control	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	21
470	10	create	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	21
471	10	approve	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	21
472	10	reject	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	21
473	10	view	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	21
474	10	modify	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	21
475	10	remove	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	21
476	7	@control	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	21
477	7	create	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	21
478	7	approve	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	21
479	7	reject	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	21
480	7	view	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	21
481	7	modify	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	21
482	7	remove	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	21
483	5	@control	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	21
484	5	create	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	21
485	5	approve	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	21
486	5	reject	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	21
487	5	view	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	21
488	5	modify	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	21
489	5	remove	2017-09-07 16:06:09	MapasCulturais\\Entities\\Request	21
\.


--
-- Name: pcache_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('pcache_id_seq', 489, true);


--
-- Data for Name: project; Type: TABLE DATA; Schema: public; Owner: -
--

COPY project (id, name, short_description, long_description, create_timestamp, status, agent_id, is_verified, type, parent_id, registration_from, registration_to, registration_categories, use_registrations, published_registrations, update_timestamp, subsite_id) FROM stdin;
5	Project 5	of Staff User 1	\N	2014-05-21 18:04:41	1	5	t	1	\N	\N	\N	\N	f	f	2014-05-21 17:41:23	\N
6	Project 6	of Staff User 2	\N	2014-05-21 18:04:41	1	6	f	1	\N	\N	\N	\N	f	t	2014-05-21 17:42:02	\N
7	Project 7	of Normal User 1	\N	2014-05-21 18:04:41	1	7	t	1	\N	\N	\N	\N	f	f	2014-05-21 17:42:35	\N
8	Project 8	of Normal User 1	\N	2014-05-21 18:04:41	1	8	f	1	\N	\N	\N	\N	f	t	2014-05-21 17:42:51	\N
1	Project 1	of Super Admin 1	\N	2014-05-21 18:04:41	1	1	t	1	\N	\N	\N	\N	f	t	2014-05-21 17:45:03	\N
2	Project 2	of Super Admin 2	\N	2014-05-21 18:04:41	1	2	f	1	\N	\N	\N	\N	f	t	2014-05-21 17:38:59	\N
3	Project 3	of Admin 1	\N	2014-05-21 18:04:41	1	3	t	1	\N	\N	\N	\N	f	f	2014-05-21 17:39:34	\N
4	Project 4	of Admin 2	\N	2014-05-21 18:04:41	1	4	f	1	\N	\N	\N	\N	f	f	2014-05-21 17:40:15	\N
\.


--
-- Data for Name: project_event; Type: TABLE DATA; Schema: public; Owner: -
--

COPY project_event (id, event_id, project_id, type, status) FROM stdin;
\.


--
-- Name: project_event_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('project_event_id_seq', 1, true);


--
-- Name: project_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('project_id_seq', 345, true);


--
-- Data for Name: project_meta; Type: TABLE DATA; Schema: public; Owner: -
--

COPY project_meta (object_id, key, value, id) FROM stdin;
\.


--
-- Name: project_meta_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('project_meta_id_seq', 1, false);


--
-- Name: pseudo_random_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('pseudo_random_id_seq', 1, false);


--
-- Data for Name: registration; Type: TABLE DATA; Schema: public; Owner: -
--

COPY registration (id, project_id, category, agent_id, create_timestamp, sent_timestamp, status, agents_data, subsite_id) FROM stdin;
\.


--
-- Data for Name: registration_field_configuration; Type: TABLE DATA; Schema: public; Owner: -
--

COPY registration_field_configuration (id, project_id, title, description, categories, required, field_type, field_options, max_size, display_order) FROM stdin;
\.


--
-- Name: registration_field_configuration_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('registration_field_configuration_id_seq', 1, false);


--
-- Data for Name: registration_file_configuration; Type: TABLE DATA; Schema: public; Owner: -
--

COPY registration_file_configuration (id, project_id, title, description, required, categories, display_order) FROM stdin;
\.


--
-- Name: registration_file_configuration_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('registration_file_configuration_id_seq', 1, false);


--
-- Name: registration_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('registration_id_seq', 1, false);


--
-- Data for Name: registration_meta; Type: TABLE DATA; Schema: public; Owner: -
--

COPY registration_meta (object_id, key, value, id) FROM stdin;
\.


--
-- Name: registration_meta_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('registration_meta_id_seq', 1, false);


--
-- Data for Name: request; Type: TABLE DATA; Schema: public; Owner: -
--

COPY request (id, request_uid, requester_user_id, origin_type, origin_id, destination_type, destination_id, metadata, type, create_timestamp, action_timestamp, status) FROM stdin;
18	3564e6b660d177cebb1ebb521d035978	11	MapasCulturais\\Entities\\Event	522	MapasCulturais\\Entities\\Space	1	a:2:{s:19:"event_occurrence_id";i:162;s:4:"rule";O:8:"stdClass":9:{s:7:"spaceId";s:1:"1";s:8:"startsAt";s:5:"11:11";s:8:"duration";i:11;s:6:"endsAt";s:5:"11:22";s:9:"frequency";s:4:"once";s:8:"startsOn";s:10:"2016-12-21";s:5:"until";s:0:"";s:11:"description";s:36:"Dia 21 de dezembro de 2016 às 11:11";s:5:"price";s:8:"gratuito";}}	EventOccurrence	2016-12-15 23:57:14	\N	1
20	1045c299290c5bb7b697ea313043d839	11	MapasCulturais\\Entities\\Event	522	MapasCulturais\\Entities\\Agent	8	a:2:{s:5:"class";s:42:"MapasCulturais\\Entities\\EventAgentRelation";s:10:"relationId";i:79;}	AgentRelation	2016-12-15 23:57:42	\N	1
21	add514f40efa7d3d4e98c490cbd2c59c	11	MapasCulturais\\Entities\\Event	522	MapasCulturais\\Entities\\Agent	5	a:2:{s:5:"class";s:42:"MapasCulturais\\Entities\\EventAgentRelation";s:10:"relationId";i:80;}	AgentRelation	2016-12-15 23:58:05	\N	1
\.


--
-- Name: request_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('request_id_seq', 22, true);


--
-- Name: revision_data_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('revision_data_id_seq', 516, true);


--
-- Data for Name: role; Type: TABLE DATA; Schema: public; Owner: -
--

COPY role (id, usr_id, name, subsite_id) FROM stdin;
1	1	superAdmin	\N
3	3	admin	\N
4	4	admin	\N
5	5	staff	\N
6	6	staff	\N
2	2	superAdmin	\N
\.


--
-- Name: role_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('role_id_seq', 127, true);


--
-- Data for Name: seal; Type: TABLE DATA; Schema: public; Owner: -
--

COPY seal (id, agent_id, name, short_description, long_description, valid_period, create_timestamp, status, certificate_text, update_timestamp, subsite_id) FROM stdin;
1	1	Selo Mapas	Descrição curta Selo Mapas	Descrição longa Selo Mapas	0	2016-12-15 11:55:02	1	\N	2014-05-21 17:45:03	\N
\.


--
-- Name: seal_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('seal_id_seq', 1, false);


--
-- Data for Name: seal_meta; Type: TABLE DATA; Schema: public; Owner: -
--

COPY seal_meta (id, object_id, key, value) FROM stdin;
\.


--
-- Name: seal_meta_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('seal_meta_id_seq', 1, false);


--
-- Data for Name: seal_relation; Type: TABLE DATA; Schema: public; Owner: -
--

COPY seal_relation (id, seal_id, object_id, create_timestamp, status, object_type, agent_id, owner_id, validate_date, renovation_request) FROM stdin;
1	1	5	2016-12-15 11:55:02	1	MapasCulturais\\Entities\\Agent	1	1	2016-12-15	\N
2	1	7	2016-12-15 11:55:02	1	MapasCulturais\\Entities\\Agent	1	1	2016-12-15	\N
3	1	1	2016-12-15 11:55:02	1	MapasCulturais\\Entities\\Agent	1	1	2016-12-15	\N
4	1	3	2016-12-15 11:55:02	1	MapasCulturais\\Entities\\Agent	1	1	2016-12-15	\N
5	1	1	2016-12-15 11:55:02	1	MapasCulturais\\Entities\\Space	1	1	2016-12-15	\N
6	1	7	2016-12-15 11:55:02	1	MapasCulturais\\Entities\\Space	1	1	2016-12-15	\N
7	1	5	2016-12-15 11:55:02	1	MapasCulturais\\Entities\\Space	1	1	2016-12-15	\N
8	1	3	2016-12-15 11:55:02	1	MapasCulturais\\Entities\\Space	1	1	2016-12-15	\N
9	1	1	2016-12-15 11:55:02	1	MapasCulturais\\Entities\\Project	1	1	2016-12-15	\N
10	1	3	2016-12-15 11:55:02	1	MapasCulturais\\Entities\\Project	1	1	2016-12-15	\N
11	1	5	2016-12-15 11:55:02	1	MapasCulturais\\Entities\\Project	1	1	2016-12-15	\N
12	1	7	2016-12-15 11:55:02	1	MapasCulturais\\Entities\\Project	1	1	2016-12-15	\N
13	1	1	2016-12-15 11:55:02	1	MapasCulturais\\Entities\\Event	1	1	2016-12-15	\N
14	1	3	2016-12-15 11:55:02	1	MapasCulturais\\Entities\\Event	1	1	2016-12-15	\N
15	1	5	2016-12-15 11:55:02	1	MapasCulturais\\Entities\\Event	1	1	2016-12-15	\N
16	1	7	2016-12-15 11:55:02	1	MapasCulturais\\Entities\\Event	1	1	2016-12-15	\N
\.


--
-- Name: seal_relation_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('seal_relation_id_seq', 16, true);


--
-- Data for Name: space; Type: TABLE DATA; Schema: public; Owner: -
--

COPY space (id, parent_id, location, _geo_location, name, short_description, long_description, create_timestamp, status, type, agent_id, is_verified, public, update_timestamp, subsite_id) FROM stdin;
7	\N	(-46.6915816000000063,-23.5394311999999992)	0101000020E6100000F841F1BE855847C02971C229188A37C0	Space 7	of Normal User 1		2014-05-21 18:04:38	1	20	7	t	f	2017-09-07 19:06:10	\N
8	\N	(-46.6468626999999856,-23.5466151000000004)	0101000020E610000048C09E65CC5247C0F1FF99F7EE8B37C0	Space 8	of Normal User 1		2014-05-21 18:04:38	1	22	8	f	f	2017-09-07 19:06:10	\N
6	\N	(-48.5070640999999796,-27.5887011999999991)	0101000020E6100000E03CF779E74048C0AF1D311FB5963BC0	Space 6	of Staff User 2		2014-05-21 18:04:38	1	61	6	f	f	2017-09-07 19:06:10	\N
5	\N	(-48.5102924000000257,-27.5666994999999986)	0101000020E6100000E0B2E842514148C0D828EB3713913BC0	Space 5	of Staff User 1		2014-05-21 18:04:38	1	91	5	t	f	2017-09-07 19:06:10	\N
4	\N	(-46.6499110999999971,-23.5575986999999998)	0101000020E610000058E77349305347C0C8CAD4C9BE8E37C0	Space 4	of Admin 2		2014-05-21 18:04:38	1	60	4	f	f	2017-09-07 19:06:10	\N
3	\N	(-46.6343521999999666,-23.5299146000000015)	0101000020E6100000A092F073325147C045ACB47BA88737C0	Space 3	of Admin 1		2014-05-21 18:04:38	1	10	3	t	f	2017-09-07 19:06:10	\N
2	\N	(-48.5129766000000018,-27.5906075000000008)	0101000020E6100000804E9C37A94148C0B745990D32973BC0	Space 2	of Super Admin 2		2014-05-21 18:04:38	1	10	2	f	f	2017-09-07 19:06:10	\N
1	\N	(-46.6444261999999981,-23.5443493000000004)	0101000020E6100000C0D7C68E7C5247C0BA19C9795A8B37C0	Space 1	of Super Admin 1		2014-05-21 18:04:38	1	10	1	t	f	2017-09-07 19:06:10	\N
\.


--
-- Name: space_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('space_id_seq', 491, true);


--
-- Data for Name: space_meta; Type: TABLE DATA; Schema: public; Owner: -
--

COPY space_meta (object_id, key, value, id) FROM stdin;
8	acessibilidade	Sim	2
6	acessibilidade	Sim	3
8	endereco	Rua Rego Freitas, 530 , República, 01220-010, São Paulo, SP	4
8	En_CEP	01220-010	5
8	En_Nome_Logradouro	Rua Rego Freitas	6
8	En_Num	530	7
8	En_Bairro	República	8
8	En_Municipio	São Paulo	9
8	En_Estado	SP	10
8	acessibilidade_fisica	Elevador;Rampa de acesso	11
7	acessibilidade	Não	12
7	endereco	Rua Engenheiro Francisco Azevedo, 216 , Jardim Vera Cruz, 05030-010, São Paulo, SP	13
7	En_CEP	05030-010	14
7	En_Nome_Logradouro	Rua Engenheiro Francisco Azevedo	15
7	En_Num	216	16
7	En_Bairro	Jardim Vera Cruz	17
7	En_Municipio	São Paulo	18
7	En_Estado	SP	19
6	En_CEP	88035-001	20
6	En_Bairro	Santa Mônica	24
6	En_Municipio	Florianópolis	25
6	En_Estado	SC	26
6	endereco	Avenida Madre Benvenuta, 1498 , Santa Mônica, 88035-001, Florianópolis, SC	21
6	En_Nome_Logradouro	Avenida Madre Benvenuta	22
6	En_Num	1498	23
6	acessibilidade_fisica	Rampa de acesso	27
5	endereco	Rodovia José Carlos Daux, 32 , João Paulo, 88030-000, Florianópolis, SC	28
5	En_CEP	88030-000	29
5	En_Nome_Logradouro	Rodovia José Carlos Daux	30
5	En_Num	32	31
5	En_Bairro	João Paulo	32
5	En_Municipio	Florianópolis	33
5	En_Estado	SC	34
5	acessibilidade	Não	35
4	acessibilidade	Sim	36
4	acessibilidade_fisica	Sinalização tátil;Rampa de acesso;Vaga de estacionamento exclusiva para idosos;Elevador	37
4	endereco	Rua Itapeva, 15 , Bela Vista, 01332-000, São Paulo, SP	38
4	En_CEP	01332-000	39
4	En_Nome_Logradouro	Rua Itapeva	40
4	En_Num	15	41
4	En_Bairro	Bela Vista	42
4	En_Municipio	São Paulo	43
4	En_Estado	SP	44
3	acessibilidade	Sim	45
3	acessibilidade_fisica	Elevador	46
3	endereco	Rua Três Rios, 20 , Bom Retiro, 01123-000, São Paulo, SP	47
3	En_CEP	01123-000	48
3	En_Nome_Logradouro	Rua Três Rios	49
3	En_Num	20	50
3	En_Bairro	Bom Retiro	51
3	En_Municipio	São Paulo	52
3	En_Estado	SP	53
3	sentNotification	0	1
2	endereco	Rua Tenente Jerônimo Borges, 33 , Santa Mônica, 88035-050, Florianópolis, SC	54
2	En_CEP	88035-050	55
2	En_Nome_Logradouro	Rua Tenente Jerônimo Borges	56
2	En_Num	33	57
2	En_Bairro	Santa Mônica	58
2	En_Municipio	Florianópolis	59
2	En_Estado	SC	60
2	acessibilidade	Não	61
2	acessibilidade_fisica	Estacionamento	62
1	endereco	Rua Araújo, 22 , República, 01220-020, São Paulo, SP	63
1	En_CEP	01220-020	64
1	En_Nome_Logradouro	Rua Araújo	65
1	En_Num	22	66
1	En_Bairro	República	67
1	En_Municipio	São Paulo	68
1	En_Estado	SP	69
\.


--
-- Name: space_meta_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('space_meta_id_seq', 69, true);


--
-- Data for Name: spatial_ref_sys; Type: TABLE DATA; Schema: public; Owner: -
--

COPY spatial_ref_sys (srid, auth_name, auth_srid, srtext, proj4text) FROM stdin;
\.


--
-- Data for Name: subsite; Type: TABLE DATA; Schema: public; Owner: -
--

COPY subsite (id, name, create_timestamp, status, agent_id, url, namespace, alias_url, verified_seals) FROM stdin;
\.


--
-- Name: subsite_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('subsite_id_seq', 1, false);


--
-- Data for Name: subsite_meta; Type: TABLE DATA; Schema: public; Owner: -
--

COPY subsite_meta (object_id, key, value, id) FROM stdin;
\.


--
-- Name: subsite_meta_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('subsite_meta_id_seq', 1, false);


--
-- Data for Name: term; Type: TABLE DATA; Schema: public; Owner: -
--

COPY term (id, taxonomy, term, description) FROM stdin;
42	tag	TAGUEADO	
2	area	Antropologia	DESCRIÇÃO
3	area	Arqueologia	DESCRIÇÃO
4	area	Arquitetura-Urbanismo	DESCRIÇÃO
5	area	Arquivo	DESCRIÇÃO
6	area	Artesanato	DESCRIÇÃO
7	area	Artes Visuais	DESCRIÇÃO
8	area	Cultura Negra	DESCRIÇÃO
9	area	Fotografia	DESCRIÇÃO
10	area	Jogos Eletrônicos	DESCRIÇÃO
11	area	Circo	DESCRIÇÃO
12	area	Filosofia	DESCRIÇÃO
13	area	Música	
14	area	Arte Digital	
15	area	Arte de Rua	
16	area	Esporte	
17	area	Cinema	
18	linguagem	Cinema	
19	linguagem	Música Popular	
20	linguagem	Cultura Indígena	
21	linguagem	Rádio	
43	linguagem	Artes Circenses	
\.


--
-- Name: term_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('term_id_seq', 43, true);


--
-- Data for Name: term_relation; Type: TABLE DATA; Schema: public; Owner: -
--

COPY term_relation (term_id, object_type, object_id, id) FROM stdin;
2	MapasCulturais\\Entities\\Agent	1	1
3	MapasCulturais\\Entities\\Agent	2	2
4	MapasCulturais\\Entities\\Agent	3	3
5	MapasCulturais\\Entities\\Agent	4	4
6	MapasCulturais\\Entities\\Agent	5	5
7	MapasCulturais\\Entities\\Agent	6	6
5	MapasCulturais\\Entities\\Agent	7	7
4	MapasCulturais\\Entities\\Agent	8	8
2	MapasCulturais\\Entities\\Space	8	9
4	MapasCulturais\\Entities\\Space	7	10
6	MapasCulturais\\Entities\\Space	6	11
8	MapasCulturais\\Entities\\Space	5	12
9	MapasCulturais\\Entities\\Space	4	13
10	MapasCulturais\\Entities\\Space	3	14
11	MapasCulturais\\Entities\\Space	2	15
12	MapasCulturais\\Entities\\Space	1	16
6	MapasCulturais\\Entities\\Space	7	17
13	MapasCulturais\\Entities\\Space	8	18
14	MapasCulturais\\Entities\\Space	6	19
11	MapasCulturais\\Entities\\Space	5	20
13	MapasCulturais\\Entities\\Space	5	21
14	MapasCulturais\\Entities\\Space	4	22
7	MapasCulturais\\Entities\\Space	4	23
14	MapasCulturais\\Entities\\Space	3	24
15	MapasCulturais\\Entities\\Space	2	25
16	MapasCulturais\\Entities\\Space	1	26
10	MapasCulturais\\Entities\\Space	1	27
15	MapasCulturais\\Entities\\Agent	7	28
17	MapasCulturais\\Entities\\Agent	7	29
18	MapasCulturais\\Entities\\Event	3	30
19	MapasCulturais\\Entities\\Event	5	31
20	MapasCulturais\\Entities\\Event	7	32
21	MapasCulturais\\Entities\\Event	7	33
14	MapasCulturais\\Entities\\Agent	356	56
42	MapasCulturais\\Entities\\Agent	356	57
15	MapasCulturais\\Entities\\Agent	357	58
42	MapasCulturais\\Entities\\Agent	357	59
18	MapasCulturais\\Entities\\Event	522	60
43	MapasCulturais\\Entities\\Event	522	61
\.


--
-- Name: term_relation_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('term_relation_id_seq', 61, true);


--
-- Data for Name: user_app; Type: TABLE DATA; Schema: public; Owner: -
--

COPY user_app (public_key, private_key, user_id, name, status, create_timestamp, subsite_id) FROM stdin;
\.


--
-- Data for Name: user_meta; Type: TABLE DATA; Schema: public; Owner: -
--

COPY user_meta (object_id, key, value, id) FROM stdin;
\.


--
-- Name: user_meta_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('user_meta_id_seq', 1, false);


--
-- Data for Name: usr; Type: TABLE DATA; Schema: public; Owner: -
--

COPY usr (id, auth_provider, auth_uid, email, last_login_timestamp, create_timestamp, status, profile_id) FROM stdin;
5	1	1	Staff1@local	2016-12-15 23:59:59	2014-05-21 17:41:23	1	5
6	1	1	Staff2@local	2016-12-16 00:00:15	2014-05-21 17:42:02	1	6
2	1	1	SuperAdmin2@local	2016-12-16 00:01:45	2014-05-21 17:38:59	1	2
3	1	1	Admin1@local	2016-12-16 00:04:14	2014-05-21 17:39:34	1	3
4	1	1	Admin2@local	2016-12-16 00:07:54	2014-05-21 17:40:15	1	4
7	1	1	Normal1@local	2016-12-16 00:15:48	2014-05-21 17:42:35	1	7
1	1	1	SuperAdmin1@local	2016-12-16 00:17:56	2014-05-21 17:45:03	1	1
8	1	1	Normal2@local	2016-12-16 00:19:59	2014-05-21 17:42:51	1	8
10	0	fake-58534870ed83e	new1@test.new	2016-12-16 00:20:22	2016-12-15 23:50:40	1	356
11	0	fake-585348c44c1bb	new2@test.new	2016-12-16 00:20:35	2016-12-15 23:52:04	1	357
\.


--
-- Name: usr_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('usr_id_seq', 11, true);


--
-- Name: _mesoregiao _mesoregiao_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY _mesoregiao
    ADD CONSTRAINT _mesoregiao_pkey PRIMARY KEY (gid);


--
-- Name: _microregiao _microregiao_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY _microregiao
    ADD CONSTRAINT _microregiao_pkey PRIMARY KEY (gid);


--
-- Name: _municipios _municipios_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY _municipios
    ADD CONSTRAINT _municipios_pkey PRIMARY KEY (gid);


--
-- Name: agent_meta agent_meta_pk; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY agent_meta
    ADD CONSTRAINT agent_meta_pk PRIMARY KEY (id);


--
-- Name: agent agent_pk; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY agent
    ADD CONSTRAINT agent_pk PRIMARY KEY (id);


--
-- Name: agent_relation agent_relation_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY agent_relation
    ADD CONSTRAINT agent_relation_pkey PRIMARY KEY (id);


--
-- Name: db_update db_update_pk; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY db_update
    ADD CONSTRAINT db_update_pk PRIMARY KEY (name);


--
-- Name: entity_revision_data entity_revision_data_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY entity_revision_data
    ADD CONSTRAINT entity_revision_data_pkey PRIMARY KEY (id);


--
-- Name: entity_revision entity_revision_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY entity_revision
    ADD CONSTRAINT entity_revision_pkey PRIMARY KEY (id);


--
-- Name: entity_revision_revision_data entity_revision_revision_data_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY entity_revision_revision_data
    ADD CONSTRAINT entity_revision_revision_data_pkey PRIMARY KEY (revision_id, revision_data_id);


--
-- Name: event_meta event_meta_pk; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY event_meta
    ADD CONSTRAINT event_meta_pk PRIMARY KEY (id);


--
-- Name: event_occurrence_cancellation event_occurrence_cancellation_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY event_occurrence_cancellation
    ADD CONSTRAINT event_occurrence_cancellation_pkey PRIMARY KEY (id);


--
-- Name: event_occurrence event_occurrence_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY event_occurrence
    ADD CONSTRAINT event_occurrence_pkey PRIMARY KEY (id);


--
-- Name: event_occurrence_recurrence event_occurrence_recurrence_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY event_occurrence_recurrence
    ADD CONSTRAINT event_occurrence_recurrence_pkey PRIMARY KEY (id);


--
-- Name: event event_pk; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY event
    ADD CONSTRAINT event_pk PRIMARY KEY (id);


--
-- Name: file file_pk; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY file
    ADD CONSTRAINT file_pk PRIMARY KEY (id);


--
-- Name: geo_division geo_divisions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY geo_division
    ADD CONSTRAINT geo_divisions_pkey PRIMARY KEY (id);


--
-- Name: metadata metadata_pk; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY metadata
    ADD CONSTRAINT metadata_pk PRIMARY KEY (object_id, object_type, key);


--
-- Name: metalist metalist_pk; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY metalist
    ADD CONSTRAINT metalist_pk PRIMARY KEY (id);


--
-- Name: notification_meta notification_meta_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY notification_meta
    ADD CONSTRAINT notification_meta_pkey PRIMARY KEY (id);


--
-- Name: notification notification_pk; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY notification
    ADD CONSTRAINT notification_pk PRIMARY KEY (id);


--
-- Name: pcache pcache_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY pcache
    ADD CONSTRAINT pcache_pkey PRIMARY KEY (id);


--
-- Name: project_event project_event_pk; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY project_event
    ADD CONSTRAINT project_event_pk PRIMARY KEY (id);


--
-- Name: project_meta project_meta_pk; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY project_meta
    ADD CONSTRAINT project_meta_pk PRIMARY KEY (id);


--
-- Name: project project_pk; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY project
    ADD CONSTRAINT project_pk PRIMARY KEY (id);


--
-- Name: registration_field_configuration registration_field_configuration_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY registration_field_configuration
    ADD CONSTRAINT registration_field_configuration_pkey PRIMARY KEY (id);


--
-- Name: registration_file_configuration registration_file_configuration_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY registration_file_configuration
    ADD CONSTRAINT registration_file_configuration_pkey PRIMARY KEY (id);


--
-- Name: registration_meta registration_meta_pk; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY registration_meta
    ADD CONSTRAINT registration_meta_pk PRIMARY KEY (id);


--
-- Name: registration registration_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY registration
    ADD CONSTRAINT registration_pkey PRIMARY KEY (id);


--
-- Name: request request_pk; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY request
    ADD CONSTRAINT request_pk PRIMARY KEY (id);


--
-- Name: role role_pk; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY role
    ADD CONSTRAINT role_pk PRIMARY KEY (id);


--
-- Name: subsite saas_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY subsite
    ADD CONSTRAINT saas_pkey PRIMARY KEY (id);


--
-- Name: seal_meta seal_meta_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY seal_meta
    ADD CONSTRAINT seal_meta_pkey PRIMARY KEY (id);


--
-- Name: seal seal_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY seal
    ADD CONSTRAINT seal_pkey PRIMARY KEY (id);


--
-- Name: seal_relation seal_relation_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY seal_relation
    ADD CONSTRAINT seal_relation_pkey PRIMARY KEY (id);


--
-- Name: space_meta space_meta_pk; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY space_meta
    ADD CONSTRAINT space_meta_pk PRIMARY KEY (id);


--
-- Name: space space_pk; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY space
    ADD CONSTRAINT space_pk PRIMARY KEY (id);


--
-- Name: subsite_meta subsite_meta_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY subsite_meta
    ADD CONSTRAINT subsite_meta_pkey PRIMARY KEY (id);


--
-- Name: term term_pk; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY term
    ADD CONSTRAINT term_pk PRIMARY KEY (id);


--
-- Name: term_relation term_relation_pk; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY term_relation
    ADD CONSTRAINT term_relation_pk PRIMARY KEY (id);


--
-- Name: user_app user_app_pk; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY user_app
    ADD CONSTRAINT user_app_pk PRIMARY KEY (public_key);


--
-- Name: user_meta user_meta_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY user_meta
    ADD CONSTRAINT user_meta_pkey PRIMARY KEY (id);


--
-- Name: usr usr_pk; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY usr
    ADD CONSTRAINT usr_pk PRIMARY KEY (id);


--
-- Name: agent_meta_key_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX agent_meta_key_idx ON agent_meta USING btree (key);


--
-- Name: agent_meta_owner_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX agent_meta_owner_idx ON agent_meta USING btree (object_id);


--
-- Name: agent_meta_owner_key_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX agent_meta_owner_key_idx ON agent_meta USING btree (object_id, key);


--
-- Name: agent_relation_all; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX agent_relation_all ON agent_relation USING btree (agent_id, object_type, object_id);


--
-- Name: alias_url_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX alias_url_index ON subsite USING btree (alias_url);


--
-- Name: event_meta_key_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX event_meta_key_idx ON event_meta USING btree (key);


--
-- Name: event_meta_owner_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX event_meta_owner_idx ON event_meta USING btree (object_id);


--
-- Name: event_meta_owner_key_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX event_meta_owner_key_idx ON event_meta USING btree (object_id, key);


--
-- Name: event_occurrence_status_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX event_occurrence_status_index ON event_occurrence USING btree (status);


--
-- Name: file_group_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX file_group_index ON file USING btree (grp);


--
-- Name: file_owner_grp_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX file_owner_grp_index ON file USING btree (object_type, object_id, grp);


--
-- Name: file_owner_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX file_owner_index ON file USING btree (object_type, object_id);


--
-- Name: geo_divisions_geom_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX geo_divisions_geom_idx ON geo_division USING gist (geom);


--
-- Name: idx_22781144c79c849a; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_22781144c79c849a ON user_app USING btree (subsite_id);


--
-- Name: idx_268b9c9dc79c849a; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_268b9c9dc79c849a ON agent USING btree (subsite_id);


--
-- Name: idx_2972c13ac79c849a; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_2972c13ac79c849a ON space USING btree (subsite_id);


--
-- Name: idx_2e30ae30c79c849a; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_2e30ae30c79c849a ON seal USING btree (subsite_id);


--
-- Name: idx_2fb3d0eec79c849a; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_2fb3d0eec79c849a ON project USING btree (subsite_id);


--
-- Name: idx_3bae0aa7c79c849a; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_3bae0aa7c79c849a ON event USING btree (subsite_id);


--
-- Name: idx_3d853098232d562b; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_3d853098232d562b ON pcache USING btree (object_id);


--
-- Name: idx_3d853098a76ed395; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_3d853098a76ed395 ON pcache USING btree (user_id);


--
-- Name: idx_57698a6ac79c849a; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_57698a6ac79c849a ON role USING btree (subsite_id);


--
-- Name: idx_60c85cb1166d1f9c; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_60c85cb1166d1f9c ON registration_field_configuration USING btree (project_id);


--
-- Name: idx_62a8a7a7c79c849a; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_62a8a7a7c79c849a ON registration USING btree (subsite_id);


--
-- Name: notification_meta_key_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX notification_meta_key_idx ON notification_meta USING btree (key);


--
-- Name: notification_meta_owner_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX notification_meta_owner_idx ON notification_meta USING btree (object_id);


--
-- Name: notification_meta_owner_key_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX notification_meta_owner_key_idx ON notification_meta USING btree (object_id, key);


--
-- Name: owner_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX owner_index ON term_relation USING btree (object_type, object_id);


--
-- Name: pcache_owner_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX pcache_owner_idx ON pcache USING btree (object_type, object_id);


--
-- Name: pcache_permission_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX pcache_permission_idx ON pcache USING btree (object_type, object_id, action);


--
-- Name: pcache_permission_user_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX pcache_permission_user_idx ON pcache USING btree (object_type, object_id, action, user_id);


--
-- Name: project_meta_key_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX project_meta_key_idx ON project_meta USING btree (key);


--
-- Name: project_meta_owner_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX project_meta_owner_idx ON project_meta USING btree (object_id);


--
-- Name: project_meta_owner_key_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX project_meta_owner_key_idx ON project_meta USING btree (object_id, key);


--
-- Name: registration_meta_key_value_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX registration_meta_key_value_idx ON registration_meta USING btree (key, value);


--
-- Name: registration_meta_owner_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX registration_meta_owner_idx ON registration_meta USING btree (object_id);


--
-- Name: registration_meta_owner_key_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX registration_meta_owner_key_idx ON registration_meta USING btree (object_id, key);


--
-- Name: request_uid; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX request_uid ON request USING btree (request_uid);


--
-- Name: requester_user_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX requester_user_index ON request USING btree (requester_user_id, origin_type, origin_id);


--
-- Name: seal_meta_key_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX seal_meta_key_idx ON seal_meta USING btree (key);


--
-- Name: seal_meta_owner_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX seal_meta_owner_idx ON seal_meta USING btree (object_id);


--
-- Name: seal_meta_owner_key_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX seal_meta_owner_key_idx ON seal_meta USING btree (object_id, key);


--
-- Name: space_location; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX space_location ON space USING gist (_geo_location);


--
-- Name: space_meta_key_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX space_meta_key_idx ON space_meta USING btree (key);


--
-- Name: space_meta_owner_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX space_meta_owner_idx ON space_meta USING btree (object_id);


--
-- Name: space_meta_owner_key_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX space_meta_owner_key_idx ON space_meta USING btree (object_id, key);


--
-- Name: space_type; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX space_type ON space USING btree (type);


--
-- Name: subsite_meta_key_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX subsite_meta_key_idx ON subsite_meta USING btree (key);


--
-- Name: subsite_meta_owner_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX subsite_meta_owner_idx ON subsite_meta USING btree (object_id);


--
-- Name: subsite_meta_owner_key_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX subsite_meta_owner_key_idx ON subsite_meta USING btree (object_id, key);


--
-- Name: taxonomy_term_unique; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX taxonomy_term_unique ON term USING btree (taxonomy, term);


--
-- Name: url_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX url_index ON subsite USING btree (url);


--
-- Name: user_meta_key_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX user_meta_key_idx ON user_meta USING btree (key);


--
-- Name: user_meta_owner_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX user_meta_owner_idx ON user_meta USING btree (object_id);


--
-- Name: user_meta_owner_key_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX user_meta_owner_key_idx ON user_meta USING btree (object_id, key);


--
-- Name: agent agent_agent_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY agent
    ADD CONSTRAINT agent_agent_fk FOREIGN KEY (parent_id) REFERENCES agent(id);


--
-- Name: agent_relation agent_relation_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY agent_relation
    ADD CONSTRAINT agent_relation_fk FOREIGN KEY (agent_id) REFERENCES agent(id);


--
-- Name: entity_revision entity_revision_usr_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY entity_revision
    ADD CONSTRAINT entity_revision_usr_fk FOREIGN KEY (user_id) REFERENCES usr(id);


--
-- Name: event event_agent_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY event
    ADD CONSTRAINT event_agent_fk FOREIGN KEY (agent_id) REFERENCES agent(id);


--
-- Name: event_occurrence event_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY event_occurrence
    ADD CONSTRAINT event_fk FOREIGN KEY (event_id) REFERENCES event(id);


--
-- Name: event_occurrence_cancellation event_occurrence_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY event_occurrence_cancellation
    ADD CONSTRAINT event_occurrence_fk FOREIGN KEY (event_occurrence_id) REFERENCES event_occurrence(id) ON DELETE CASCADE;


--
-- Name: event_occurrence_recurrence event_occurrence_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY event_occurrence_recurrence
    ADD CONSTRAINT event_occurrence_fk FOREIGN KEY (event_occurrence_id) REFERENCES event_occurrence(id) ON DELETE CASCADE;


--
-- Name: project_event event_project_event_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY project_event
    ADD CONSTRAINT event_project_event_fk FOREIGN KEY (event_id) REFERENCES event(id);


--
-- Name: file file_file_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY file
    ADD CONSTRAINT file_file_fk FOREIGN KEY (parent_id) REFERENCES file(id);


--
-- Name: registration_meta fk_18cc03e9232d562b; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY registration_meta
    ADD CONSTRAINT fk_18cc03e9232d562b FOREIGN KEY (object_id) REFERENCES registration(id) ON DELETE CASCADE;


--
-- Name: registration_file_configuration fk_209c792e166d1f9c; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY registration_file_configuration
    ADD CONSTRAINT fk_209c792e166d1f9c FOREIGN KEY (project_id) REFERENCES project(id);


--
-- Name: user_app fk_22781144c79c849a; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY user_app
    ADD CONSTRAINT fk_22781144c79c849a FOREIGN KEY (subsite_id) REFERENCES subsite(id);


--
-- Name: agent fk_268b9c9dc79c849a; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY agent
    ADD CONSTRAINT fk_268b9c9dc79c849a FOREIGN KEY (subsite_id) REFERENCES subsite(id);


--
-- Name: space fk_2972c13ac79c849a; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY space
    ADD CONSTRAINT fk_2972c13ac79c849a FOREIGN KEY (subsite_id) REFERENCES subsite(id);


--
-- Name: seal fk_2e30ae30c79c849a; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY seal
    ADD CONSTRAINT fk_2e30ae30c79c849a FOREIGN KEY (subsite_id) REFERENCES subsite(id);


--
-- Name: project fk_2fb3d0eec79c849a; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY project
    ADD CONSTRAINT fk_2fb3d0eec79c849a FOREIGN KEY (subsite_id) REFERENCES subsite(id);


--
-- Name: event fk_3bae0aa7c79c849a; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY event
    ADD CONSTRAINT fk_3bae0aa7c79c849a FOREIGN KEY (subsite_id) REFERENCES subsite(id);


--
-- Name: pcache fk_3d853098a76ed395; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY pcache
    ADD CONSTRAINT fk_3d853098a76ed395 FOREIGN KEY (user_id) REFERENCES usr(id);


--
-- Name: role fk_57698a6ac69d3fb; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY role
    ADD CONSTRAINT fk_57698a6ac69d3fb FOREIGN KEY (usr_id) REFERENCES usr(id);


--
-- Name: role fk_57698a6ac79c849a; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY role
    ADD CONSTRAINT fk_57698a6ac79c849a FOREIGN KEY (subsite_id) REFERENCES subsite(id);


--
-- Name: registration_field_configuration fk_60c85cb1166d1f9c; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY registration_field_configuration
    ADD CONSTRAINT fk_60c85cb1166d1f9c FOREIGN KEY (project_id) REFERENCES project(id);


--
-- Name: registration fk_62a8a7a7c79c849a; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY registration
    ADD CONSTRAINT fk_62a8a7a7c79c849a FOREIGN KEY (subsite_id) REFERENCES subsite(id);


--
-- Name: notification_meta fk_6fce5f0f232d562b; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY notification_meta
    ADD CONSTRAINT fk_6fce5f0f232d562b FOREIGN KEY (object_id) REFERENCES notification(id) ON DELETE CASCADE;


--
-- Name: subsite_meta fk_780702f5232d562b; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY subsite_meta
    ADD CONSTRAINT fk_780702f5232d562b FOREIGN KEY (object_id) REFERENCES subsite(id) ON DELETE CASCADE;


--
-- Name: agent_meta fk_7a69aed6232d562b; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY agent_meta
    ADD CONSTRAINT fk_7a69aed6232d562b FOREIGN KEY (object_id) REFERENCES agent(id) ON DELETE CASCADE;


--
-- Name: seal_meta fk_a92e5e22232d562b; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY seal_meta
    ADD CONSTRAINT fk_a92e5e22232d562b FOREIGN KEY (object_id) REFERENCES seal(id) ON DELETE CASCADE;


--
-- Name: user_meta fk_ad7358fc232d562b; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY user_meta
    ADD CONSTRAINT fk_ad7358fc232d562b FOREIGN KEY (object_id) REFERENCES usr(id) ON DELETE CASCADE;


--
-- Name: space_meta fk_bc846ebf232d562b; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY space_meta
    ADD CONSTRAINT fk_bc846ebf232d562b FOREIGN KEY (object_id) REFERENCES space(id) ON DELETE CASCADE;


--
-- Name: event_meta fk_c839589e232d562b; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY event_meta
    ADD CONSTRAINT fk_c839589e232d562b FOREIGN KEY (object_id) REFERENCES event(id) ON DELETE CASCADE;


--
-- Name: project_meta fk_ee63dc2d232d562b; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY project_meta
    ADD CONSTRAINT fk_ee63dc2d232d562b FOREIGN KEY (object_id) REFERENCES project(id) ON DELETE CASCADE;


--
-- Name: notification notification_request_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY notification
    ADD CONSTRAINT notification_request_fk FOREIGN KEY (request_id) REFERENCES request(id);


--
-- Name: notification notification_user_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY notification
    ADD CONSTRAINT notification_user_fk FOREIGN KEY (user_id) REFERENCES usr(id);


--
-- Name: project project_agent_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY project
    ADD CONSTRAINT project_agent_fk FOREIGN KEY (agent_id) REFERENCES agent(id);


--
-- Name: event project_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY event
    ADD CONSTRAINT project_fk FOREIGN KEY (project_id) REFERENCES project(id);


--
-- Name: project_event project_project_event_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY project_event
    ADD CONSTRAINT project_project_event_fk FOREIGN KEY (project_id) REFERENCES project(id);


--
-- Name: project project_project_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY project
    ADD CONSTRAINT project_project_fk FOREIGN KEY (parent_id) REFERENCES project(id);


--
-- Name: request requester_user_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY request
    ADD CONSTRAINT requester_user_fk FOREIGN KEY (requester_user_id) REFERENCES usr(id);


--
-- Name: entity_revision_revision_data revision_data_entity_revision_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY entity_revision_revision_data
    ADD CONSTRAINT revision_data_entity_revision_fk FOREIGN KEY (revision_id) REFERENCES entity_revision(id);


--
-- Name: entity_revision_revision_data revision_data_revision_data_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY entity_revision_revision_data
    ADD CONSTRAINT revision_data_revision_data_fk FOREIGN KEY (revision_data_id) REFERENCES entity_revision_data(id);


--
-- Name: subsite_meta saas_saas_meta_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY subsite_meta
    ADD CONSTRAINT saas_saas_meta_fk FOREIGN KEY (object_id) REFERENCES subsite(id);


--
-- Name: seal seal_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY seal
    ADD CONSTRAINT seal_fk FOREIGN KEY (agent_id) REFERENCES agent(id);


--
-- Name: seal_relation seal_relation_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY seal_relation
    ADD CONSTRAINT seal_relation_fk FOREIGN KEY (seal_id) REFERENCES seal(id);


--
-- Name: space space_agent_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY space
    ADD CONSTRAINT space_agent_fk FOREIGN KEY (agent_id) REFERENCES agent(id);


--
-- Name: event_occurrence space_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY event_occurrence
    ADD CONSTRAINT space_fk FOREIGN KEY (space_id) REFERENCES space(id);


--
-- Name: space space_space_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY space
    ADD CONSTRAINT space_space_fk FOREIGN KEY (parent_id) REFERENCES space(id);


--
-- Name: term_relation term_term_relation_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY term_relation
    ADD CONSTRAINT term_term_relation_fk FOREIGN KEY (term_id) REFERENCES term(id);


--
-- Name: usr user_profile_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY usr
    ADD CONSTRAINT user_profile_fk FOREIGN KEY (profile_id) REFERENCES agent(id);


--
-- Name: agent usr_agent_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY agent
    ADD CONSTRAINT usr_agent_fk FOREIGN KEY (user_id) REFERENCES usr(id);


--
-- Name: user_app usr_user_app_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY user_app
    ADD CONSTRAINT usr_user_app_fk FOREIGN KEY (user_id) REFERENCES usr(id);


--
-- PostgreSQL database dump complete
--

