--
-- PostgreSQL database dump
--

-- Dumped from database version 14.8 (Debian 14.8-1.pgdg110+1)
-- Dumped by pg_dump version 14.8 (Debian 14.8-1.pgdg110+1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: tiger; Type: SCHEMA; Schema: -; Owner: mapas
--

CREATE SCHEMA tiger;


ALTER SCHEMA tiger OWNER TO mapas;

--
-- Name: tiger_data; Type: SCHEMA; Schema: -; Owner: mapas
--

CREATE SCHEMA tiger_data;


ALTER SCHEMA tiger_data OWNER TO mapas;

--
-- Name: topology; Type: SCHEMA; Schema: -; Owner: mapas
--

CREATE SCHEMA topology;


ALTER SCHEMA topology OWNER TO mapas;

--
-- Name: SCHEMA topology; Type: COMMENT; Schema: -; Owner: mapas
--

COMMENT ON SCHEMA topology IS 'PostGIS Topology schema';


--
-- Name: fuzzystrmatch; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS fuzzystrmatch WITH SCHEMA public;


--
-- Name: EXTENSION fuzzystrmatch; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION fuzzystrmatch IS 'determine similarities and distance between strings';


--
-- Name: postgis; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS postgis WITH SCHEMA public;


--
-- Name: EXTENSION postgis; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION postgis IS 'PostGIS geometry, geography, and raster spatial types and functions';


--
-- Name: postgis_tiger_geocoder; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS postgis_tiger_geocoder WITH SCHEMA tiger;


--
-- Name: EXTENSION postgis_tiger_geocoder; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION postgis_tiger_geocoder IS 'PostGIS tiger geocoder and reverse geocoder';


--
-- Name: postgis_topology; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS postgis_topology WITH SCHEMA topology;


--
-- Name: EXTENSION postgis_topology; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION postgis_topology IS 'PostGIS topology spatial types and functions';


--
-- Name: unaccent; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS unaccent WITH SCHEMA public;


--
-- Name: EXTENSION unaccent; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION unaccent IS 'text search dictionary that removes accents';


--
-- Name: frequency; Type: DOMAIN; Schema: public; Owner: mapas
--

CREATE DOMAIN public.frequency AS character varying
	CONSTRAINT frequency_check CHECK (((VALUE)::text = ANY (ARRAY[('once'::character varying)::text, ('daily'::character varying)::text, ('weekly'::character varying)::text, ('monthly'::character varying)::text, ('yearly'::character varying)::text])));


ALTER DOMAIN public.frequency OWNER TO mapas;

--
-- Name: object_type; Type: TYPE; Schema: public; Owner: mapas
--

CREATE TYPE public.object_type AS ENUM (
    'MapasCulturais\Entities\Agent',
    'MapasCulturais\Entities\ChatMessage',
    'MapasCulturais\Entities\ChatThread',
    'MapasCulturais\Entities\EvaluationMethodConfiguration',
    'MapasCulturais\Entities\Event',
    'MapasCulturais\Entities\Notification',
    'MapasCulturais\Entities\Opportunity',
    'MapasCulturais\Entities\Project',
    'MapasCulturais\Entities\Registration',
    'MapasCulturais\Entities\RegistrationEvaluation',
    'MapasCulturais\Entities\RegistrationFileConfiguration',
    'MapasCulturais\Entities\Request',
    'MapasCulturais\Entities\Seal',
    'MapasCulturais\Entities\Space',
    'MapasCulturais\Entities\Subsite',
    'MapasCulturais\Entities\User',
    'UserManagement\Entities\SystemRole'
);


ALTER TYPE public.object_type OWNER TO mapas;

--
-- Name: permission_action; Type: TYPE; Schema: public; Owner: mapas
--

CREATE TYPE public.permission_action AS ENUM (
    'approve',
    'archive',
    'changeOwner',
    'changeStatus',
    '@control',
    'create',
    'createAgentRelation',
    'createAgentRelationWithControl',
    'createEvents',
    'createSealRelation',
    'createSpaceRelation',
    'destroy',
    'evaluate',
    'evaluateRegistrations',
    'modify',
    'modifyRegistrationFields',
    'modifyValuers',
    'post',
    'publish',
    'publishRegistrations',
    'register',
    'reject',
    'remove',
    'removeAgentRelation',
    'removeAgentRelationWithControl',
    'removeSealRelation',
    'removeSpaceRelation',
    'reopenValuerEvaluations',
    'requestEventRelation',
    'send',
    'sendUserEvaluations',
    'unpublish',
    'view',
    'viewConsolidatedResult',
    'viewEvaluations',
    'viewPrivateData',
    'viewPrivateFiles',
    'viewRegistrations',
    'viewUserEvaluation',
    'changeType',
    'changeUserProfile',
    'deleteAccount',
    'evaluateOnTime',
    'unarchive',
    'changePassword'
);


ALTER TYPE public.permission_action OWNER TO mapas;

--
-- Name: CAST (point AS text); Type: CAST; Schema: -; Owner: -
--

-- CREATE CAST (point AS text) WITH FUNCTION pg_catalog.text(point) AS IMPLICIT;


--
-- Name: days_in_month(date); Type: FUNCTION; Schema: public; Owner: mapas
--

CREATE FUNCTION public.days_in_month(check_date date) RETURNS integer
    LANGUAGE plpgsql IMMUTABLE
    AS $$
DECLARE
  first_of_month DATE := check_date - ((extract(day from check_date) - 1)||' days')::interval;
BEGIN
  RETURN extract(day from first_of_month + '1 month'::interval - first_of_month);
END;
$$;


ALTER FUNCTION public.days_in_month(check_date date) OWNER TO mapas;

--
-- Name: fn_clean_orphans(); Type: FUNCTION; Schema: public; Owner: mapas
--

CREATE FUNCTION public.fn_clean_orphans() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
                        DECLARE _p_type VARCHAR;
                    BEGIN
                        _p_type=TG_ARGV[0];
                        DELETE FROM agent_relation WHERE
                            object_type::varchar=_p_type AND object_id=OLD.id;
                        DELETE FROM seal_relation WHERE
                            object_type=_p_type AND object_id=OLD.id;   
                        DELETE FROM space_relation WHERE
                            object_type=_p_type AND object_id=OLD.id;
                        DELETE FROM term_relation WHERE
                            object_type::varchar=_p_type AND object_id=OLD.id;
                        DELETE FROM metalist WHERE
                            object_type=_p_type AND object_id=OLD.id;
                        DELETE FROM file WHERE
                            object_type::varchar=_p_type AND object_id=OLD.id;
                        DELETE FROM chat_thread WHERE
                            object_type=_p_type AND object_id=OLD.id;
                        DELETE FROM pcache WHERE
                            object_type::varchar=_p_type AND object_id=OLD.id;
                        DELETE FROM request WHERE
                            (origin_type=_p_type AND origin_id=OLD.id) OR
                            (destination_type=_p_type AND destination_id=OLD.id);
                        RETURN NULL;
                    END; $$;


ALTER FUNCTION public.fn_clean_orphans() OWNER TO mapas;

--
-- Name: generate_recurrences(interval, date, date, date, date, integer, integer, integer); Type: FUNCTION; Schema: public; Owner: mapas
--

CREATE FUNCTION public.generate_recurrences(duration interval, original_start_date date, original_end_date date, range_start date, range_end date, repeat_month integer, repeat_week integer, repeat_day integer) RETURNS SETOF date
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


ALTER FUNCTION public.generate_recurrences(duration interval, original_start_date date, original_end_date date, range_start date, range_end date, repeat_month integer, repeat_week integer, repeat_day integer) OWNER TO mapas;

--
-- Name: interval_for(public.frequency); Type: FUNCTION; Schema: public; Owner: mapas
--

CREATE FUNCTION public.interval_for(recurs public.frequency) RETURNS interval
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


ALTER FUNCTION public.interval_for(recurs public.frequency) OWNER TO mapas;

--
-- Name: intervals_between(date, date, interval); Type: FUNCTION; Schema: public; Owner: mapas
--

CREATE FUNCTION public.intervals_between(start_date date, end_date date, duration interval) RETURNS double precision
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


ALTER FUNCTION public.intervals_between(start_date date, end_date date, duration interval) OWNER TO mapas;

--
-- Name: pseudo_random_id_generator(); Type: FUNCTION; Schema: public; Owner: mapas
--

CREATE FUNCTION public.pseudo_random_id_generator() RETURNS integer
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


ALTER FUNCTION public.pseudo_random_id_generator() OWNER TO mapas;

--
-- Name: random_id_generator(character varying, bigint); Type: FUNCTION; Schema: public; Owner: mapas
--

CREATE FUNCTION public.random_id_generator(table_name character varying, initial_range bigint) RETURNS bigint
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


ALTER FUNCTION public.random_id_generator(table_name character varying, initial_range bigint) OWNER TO mapas;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: event_occurrence; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.event_occurrence (
    id integer NOT NULL,
    space_id integer NOT NULL,
    event_id integer NOT NULL,
    rule text,
    starts_on date,
    ends_on date,
    starts_at timestamp without time zone,
    ends_at timestamp without time zone,
    frequency public.frequency,
    separation integer DEFAULT 1 NOT NULL,
    count integer,
    until date,
    timezone_name text DEFAULT 'Etc/UTC'::text NOT NULL,
    status integer DEFAULT 1 NOT NULL,
    description text,
    price text,
    priceinfo text,
    CONSTRAINT positive_separation CHECK ((separation > 0))
);


ALTER TABLE public.event_occurrence OWNER TO mapas;

--
-- Name: recurrences_for(public.event_occurrence, timestamp without time zone, timestamp without time zone); Type: FUNCTION; Schema: public; Owner: mapas
--

CREATE FUNCTION public.recurrences_for(event public.event_occurrence, range_start timestamp without time zone, range_end timestamp without time zone) RETURNS SETOF date
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


ALTER FUNCTION public.recurrences_for(event public.event_occurrence, range_start timestamp without time zone, range_end timestamp without time zone) OWNER TO mapas;

--
-- Name: recurring_event_occurrence_for(timestamp without time zone, timestamp without time zone, character varying, integer); Type: FUNCTION; Schema: public; Owner: mapas
--

CREATE FUNCTION public.recurring_event_occurrence_for(range_start timestamp without time zone, range_end timestamp without time zone, time_zone character varying, event_occurrence_limit integer) RETURNS SETOF public.event_occurrence
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


ALTER FUNCTION public.recurring_event_occurrence_for(range_start timestamp without time zone, range_end timestamp without time zone, time_zone character varying, event_occurrence_limit integer) OWNER TO mapas;

--
-- Name: _mesoregiao; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public._mesoregiao (
    gid integer NOT NULL,
    id double precision,
    nm_meso character varying(100),
    cd_geocodu character varying(2),
    geom public.geometry(MultiPolygon,4326)
);


ALTER TABLE public._mesoregiao OWNER TO mapas;

--
-- Name: _mesoregiao_gid_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public._mesoregiao_gid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public._mesoregiao_gid_seq OWNER TO mapas;

--
-- Name: _mesoregiao_gid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: mapas
--

ALTER SEQUENCE public._mesoregiao_gid_seq OWNED BY public._mesoregiao.gid;


--
-- Name: _microregiao; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public._microregiao (
    gid integer NOT NULL,
    id double precision,
    nm_micro character varying(100),
    cd_geocodu character varying(2),
    geom public.geometry(MultiPolygon,4326)
);


ALTER TABLE public._microregiao OWNER TO mapas;

--
-- Name: _microregiao_gid_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public._microregiao_gid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public._microregiao_gid_seq OWNER TO mapas;

--
-- Name: _microregiao_gid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: mapas
--

ALTER SEQUENCE public._microregiao_gid_seq OWNED BY public._microregiao.gid;


--
-- Name: _municipios; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public._municipios (
    gid integer NOT NULL,
    id double precision,
    cd_geocodm character varying(20),
    nm_municip character varying(60),
    geom public.geometry(MultiPolygon,4326)
);


ALTER TABLE public._municipios OWNER TO mapas;

--
-- Name: _municipios_gid_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public._municipios_gid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public._municipios_gid_seq OWNER TO mapas;

--
-- Name: _municipios_gid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: mapas
--

ALTER SEQUENCE public._municipios_gid_seq OWNED BY public._municipios.gid;


--
-- Name: agent_id_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public.agent_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.agent_id_seq OWNER TO mapas;

--
-- Name: agent; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.agent (
    id integer DEFAULT nextval('public.agent_id_seq'::regclass) NOT NULL,
    parent_id integer,
    user_id integer NOT NULL,
    type smallint NOT NULL,
    name character varying(255) NOT NULL,
    location point,
    _geo_location public.geography,
    short_description text,
    long_description text,
    create_timestamp timestamp without time zone NOT NULL,
    status smallint NOT NULL,
    is_verified boolean DEFAULT false NOT NULL,
    public_location boolean,
    update_timestamp timestamp(0) without time zone,
    subsite_id integer
);


ALTER TABLE public.agent OWNER TO mapas;

--
-- Name: COLUMN agent.location; Type: COMMENT; Schema: public; Owner: mapas
--

COMMENT ON COLUMN public.agent.location IS 'type=POINT';


--
-- Name: agent_meta; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.agent_meta (
    object_id integer NOT NULL,
    key character varying(255) NOT NULL,
    value text,
    id integer NOT NULL
);


ALTER TABLE public.agent_meta OWNER TO mapas;

--
-- Name: agent_meta_id_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public.agent_meta_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.agent_meta_id_seq OWNER TO mapas;

--
-- Name: agent_meta_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: mapas
--

ALTER SEQUENCE public.agent_meta_id_seq OWNED BY public.agent_meta.id;


--
-- Name: agent_relation; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.agent_relation (
    id integer NOT NULL,
    agent_id integer NOT NULL,
    object_type public.object_type NOT NULL,
    object_id integer NOT NULL,
    type character varying(64),
    has_control boolean DEFAULT false NOT NULL,
    create_timestamp timestamp without time zone,
    status smallint,
    metadata json
);


ALTER TABLE public.agent_relation OWNER TO mapas;

--
-- Name: agent_relation_id_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public.agent_relation_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.agent_relation_id_seq OWNER TO mapas;

--
-- Name: agent_relation_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: mapas
--

ALTER SEQUENCE public.agent_relation_id_seq OWNED BY public.agent_relation.id;


--
-- Name: chat_message; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.chat_message (
    id integer NOT NULL,
    chat_thread_id integer NOT NULL,
    parent_id integer,
    user_id integer NOT NULL,
    payload text NOT NULL,
    create_timestamp timestamp(0) without time zone NOT NULL
);


ALTER TABLE public.chat_message OWNER TO mapas;

--
-- Name: chat_message_id_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public.chat_message_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.chat_message_id_seq OWNER TO mapas;

--
-- Name: chat_thread; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.chat_thread (
    id integer NOT NULL,
    object_id integer NOT NULL,
    object_type character varying(255) NOT NULL,
    type character varying(255) NOT NULL,
    identifier character varying(255) NOT NULL,
    description text,
    create_timestamp timestamp(0) without time zone NOT NULL,
    last_message_timestamp timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    status integer NOT NULL
);


ALTER TABLE public.chat_thread OWNER TO mapas;

--
-- Name: COLUMN chat_thread.object_type; Type: COMMENT; Schema: public; Owner: mapas
--

COMMENT ON COLUMN public.chat_thread.object_type IS '(DC2Type:object_type)';


--
-- Name: chat_thread_id_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public.chat_thread_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.chat_thread_id_seq OWNER TO mapas;

--
-- Name: db_update; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.db_update (
    name character varying(255) NOT NULL,
    exec_time timestamp without time zone DEFAULT now() NOT NULL
);


ALTER TABLE public.db_update OWNER TO mapas;

--
-- Name: entity_revision; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.entity_revision (
    id integer NOT NULL,
    user_id integer,
    object_id integer NOT NULL,
    object_type public.object_type NOT NULL,
    create_timestamp timestamp(0) without time zone NOT NULL,
    action character varying(255) NOT NULL,
    message text NOT NULL
);


ALTER TABLE public.entity_revision OWNER TO mapas;

--
-- Name: entity_revision_data; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.entity_revision_data (
    id integer NOT NULL,
    "timestamp" timestamp(0) without time zone NOT NULL,
    key character varying(255) NOT NULL,
    value text
);


ALTER TABLE public.entity_revision_data OWNER TO mapas;

--
-- Name: entity_revision_id_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public.entity_revision_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.entity_revision_id_seq OWNER TO mapas;

--
-- Name: entity_revision_revision_data; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.entity_revision_revision_data (
    revision_id integer NOT NULL,
    revision_data_id integer NOT NULL
);


ALTER TABLE public.entity_revision_revision_data OWNER TO mapas;

--
-- Name: evaluation_method_configuration; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.evaluation_method_configuration (
    id integer NOT NULL,
    opportunity_id integer NOT NULL,
    type character varying(255) NOT NULL,
    evaluation_from timestamp without time zone,
    evaluation_to timestamp without time zone,
    name character varying(255) DEFAULT NULL::character varying
);


ALTER TABLE public.evaluation_method_configuration OWNER TO mapas;

--
-- Name: evaluation_method_configuration_id_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public.evaluation_method_configuration_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.evaluation_method_configuration_id_seq OWNER TO mapas;

--
-- Name: evaluation_method_configuration_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: mapas
--

ALTER SEQUENCE public.evaluation_method_configuration_id_seq OWNED BY public.evaluation_method_configuration.id;


--
-- Name: evaluationmethodconfiguration_meta; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.evaluationmethodconfiguration_meta (
    id integer NOT NULL,
    object_id integer NOT NULL,
    key character varying(255) NOT NULL,
    value text
);


ALTER TABLE public.evaluationmethodconfiguration_meta OWNER TO mapas;

--
-- Name: evaluationmethodconfiguration_meta_id_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public.evaluationmethodconfiguration_meta_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.evaluationmethodconfiguration_meta_id_seq OWNER TO mapas;

--
-- Name: pcache_id_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public.pcache_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.pcache_id_seq OWNER TO mapas;

--
-- Name: pcache; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.pcache (
    id integer DEFAULT nextval('public.pcache_id_seq'::regclass) NOT NULL,
    user_id integer NOT NULL,
    action public.permission_action NOT NULL,
    create_timestamp timestamp(0) without time zone NOT NULL,
    object_type public.object_type NOT NULL,
    object_id integer
);


ALTER TABLE public.pcache OWNER TO mapas;

--
-- Name: registration; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.registration (
    id integer DEFAULT public.pseudo_random_id_generator() NOT NULL,
    opportunity_id integer NOT NULL,
    category character varying(255),
    agent_id integer NOT NULL,
    create_timestamp timestamp without time zone DEFAULT now() NOT NULL,
    sent_timestamp timestamp without time zone,
    status smallint NOT NULL,
    agents_data text,
    subsite_id integer,
    consolidated_result character varying(255) DEFAULT NULL::character varying,
    number character varying(24),
    valuers_exceptions_list text DEFAULT '{"include": [], "exclude": []}'::text NOT NULL,
    space_data text
);


ALTER TABLE public.registration OWNER TO mapas;

--
-- Name: registration_evaluation; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.registration_evaluation (
    id integer NOT NULL,
    registration_id integer DEFAULT public.pseudo_random_id_generator() NOT NULL,
    user_id integer NOT NULL,
    result character varying(255) DEFAULT NULL::character varying,
    evaluation_data text NOT NULL,
    status smallint,
    create_timestamp timestamp without time zone DEFAULT now() NOT NULL,
    update_timestamp timestamp without time zone
);


ALTER TABLE public.registration_evaluation OWNER TO mapas;

--
-- Name: usr_id_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public.usr_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.usr_id_seq OWNER TO mapas;

--
-- Name: usr; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.usr (
    id integer DEFAULT nextval('public.usr_id_seq'::regclass) NOT NULL,
    auth_provider smallint NOT NULL,
    auth_uid character varying(512) NOT NULL,
    email character varying(255) NOT NULL,
    last_login_timestamp timestamp without time zone NOT NULL,
    create_timestamp timestamp without time zone DEFAULT now() NOT NULL,
    status smallint NOT NULL,
    profile_id integer
);


ALTER TABLE public.usr OWNER TO mapas;

--
-- Name: COLUMN usr.auth_provider; Type: COMMENT; Schema: public; Owner: mapas
--

COMMENT ON COLUMN public.usr.auth_provider IS '1=openid';


--
-- Name: evaluations; Type: VIEW; Schema: public; Owner: mapas
--

CREATE VIEW public.evaluations AS
 SELECT evaluations_view.registration_id,
    evaluations_view.registration_sent_timestamp,
    evaluations_view.registration_number,
    evaluations_view.registration_category,
    evaluations_view.registration_agent_id,
    evaluations_view.opportunity_id,
    evaluations_view.valuer_user_id,
    evaluations_view.valuer_agent_id,
    max(evaluations_view.evaluation_id) AS evaluation_id,
    max((evaluations_view.evaluation_result)::text) AS evaluation_result,
    max(evaluations_view.evaluation_status) AS evaluation_status
   FROM ( SELECT r.id AS registration_id,
            r.sent_timestamp AS registration_sent_timestamp,
            r.number AS registration_number,
            r.category AS registration_category,
            r.agent_id AS registration_agent_id,
            re.user_id AS valuer_user_id,
            u.profile_id AS valuer_agent_id,
            r.opportunity_id,
            re.id AS evaluation_id,
            re.result AS evaluation_result,
            re.status AS evaluation_status
           FROM ((public.registration r
             JOIN public.registration_evaluation re ON ((re.registration_id = r.id)))
             JOIN public.usr u ON ((u.id = re.user_id)))
          WHERE (r.status > 0)
        UNION
         SELECT r2.id AS registration_id,
            r2.sent_timestamp AS registration_sent_timestamp,
            r2.number AS registration_number,
            r2.category AS registration_category,
            r2.agent_id AS registration_agent_id,
            p2.user_id AS valuer_user_id,
            u2.profile_id AS valuer_agent_id,
            r2.opportunity_id,
            NULL::integer AS evaluation_id,
            NULL::character varying AS evaluation_result,
            NULL::smallint AS evaluation_status
           FROM (((public.registration r2
             JOIN public.pcache p2 ON (((p2.object_id = r2.id) AND (p2.object_type = 'MapasCulturais\Entities\Registration'::public.object_type) AND (p2.action = 'evaluateOnTime'::public.permission_action))))
             JOIN public.usr u2 ON ((u2.id = p2.user_id)))
             JOIN public.evaluation_method_configuration emc ON ((emc.opportunity_id = r2.opportunity_id)))
          WHERE (r2.status > 0)) evaluations_view
  GROUP BY evaluations_view.registration_id, evaluations_view.registration_sent_timestamp, evaluations_view.registration_number, evaluations_view.registration_category, evaluations_view.registration_agent_id, evaluations_view.valuer_user_id, evaluations_view.valuer_agent_id, evaluations_view.opportunity_id;


ALTER TABLE public.evaluations OWNER TO mapas;

--
-- Name: event; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.event (
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


ALTER TABLE public.event OWNER TO mapas;

--
-- Name: event_attendance; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.event_attendance (
    id integer NOT NULL,
    user_id integer NOT NULL,
    event_occurrence_id integer NOT NULL,
    event_id integer NOT NULL,
    space_id integer NOT NULL,
    type character varying(255) NOT NULL,
    reccurrence_string text,
    start_timestamp timestamp(0) without time zone NOT NULL,
    end_timestamp timestamp(0) without time zone NOT NULL,
    create_timestamp timestamp(0) without time zone NOT NULL
);


ALTER TABLE public.event_attendance OWNER TO mapas;

--
-- Name: event_attendance_id_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public.event_attendance_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.event_attendance_id_seq OWNER TO mapas;

--
-- Name: event_id_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public.event_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.event_id_seq OWNER TO mapas;

--
-- Name: event_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: mapas
--

ALTER SEQUENCE public.event_id_seq OWNED BY public.event.id;


--
-- Name: event_meta; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.event_meta (
    key character varying(255) NOT NULL,
    object_id integer NOT NULL,
    value text,
    id integer NOT NULL
);


ALTER TABLE public.event_meta OWNER TO mapas;

--
-- Name: event_meta_id_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public.event_meta_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.event_meta_id_seq OWNER TO mapas;

--
-- Name: event_meta_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: mapas
--

ALTER SEQUENCE public.event_meta_id_seq OWNED BY public.event_meta.id;


--
-- Name: event_occurrence_cancellation; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.event_occurrence_cancellation (
    id integer NOT NULL,
    event_occurrence_id integer,
    date date
);


ALTER TABLE public.event_occurrence_cancellation OWNER TO mapas;

--
-- Name: event_occurrence_cancellation_id_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public.event_occurrence_cancellation_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.event_occurrence_cancellation_id_seq OWNER TO mapas;

--
-- Name: event_occurrence_cancellation_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: mapas
--

ALTER SEQUENCE public.event_occurrence_cancellation_id_seq OWNED BY public.event_occurrence_cancellation.id;


--
-- Name: event_occurrence_id_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public.event_occurrence_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.event_occurrence_id_seq OWNER TO mapas;

--
-- Name: event_occurrence_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: mapas
--

ALTER SEQUENCE public.event_occurrence_id_seq OWNED BY public.event_occurrence.id;


--
-- Name: event_occurrence_recurrence; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.event_occurrence_recurrence (
    id integer NOT NULL,
    event_occurrence_id integer,
    month integer,
    day integer,
    week integer
);


ALTER TABLE public.event_occurrence_recurrence OWNER TO mapas;

--
-- Name: event_occurrence_recurrence_id_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public.event_occurrence_recurrence_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.event_occurrence_recurrence_id_seq OWNER TO mapas;

--
-- Name: event_occurrence_recurrence_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: mapas
--

ALTER SEQUENCE public.event_occurrence_recurrence_id_seq OWNED BY public.event_occurrence_recurrence.id;


--
-- Name: file_id_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public.file_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.file_id_seq OWNER TO mapas;

--
-- Name: file; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.file (
    id integer DEFAULT nextval('public.file_id_seq'::regclass) NOT NULL,
    md5 character varying(32) NOT NULL,
    mime_type character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    object_type public.object_type NOT NULL,
    object_id integer NOT NULL,
    create_timestamp timestamp without time zone DEFAULT now() NOT NULL,
    grp character varying(32) NOT NULL,
    description text,
    parent_id integer,
    path character varying(1024) DEFAULT NULL::character varying,
    private boolean DEFAULT false NOT NULL
);


ALTER TABLE public.file OWNER TO mapas;

--
-- Name: geo_division_id_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public.geo_division_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.geo_division_id_seq OWNER TO mapas;

--
-- Name: geo_division; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.geo_division (
    id integer DEFAULT nextval('public.geo_division_id_seq'::regclass) NOT NULL,
    parent_id integer,
    type character varying(32) NOT NULL,
    cod character varying(32),
    name character varying(128) NOT NULL,
    geom public.geometry,
    CONSTRAINT enforce_dims_geom CHECK ((public.st_ndims(geom) = 2)),
    CONSTRAINT enforce_geotype_geom CHECK (((public.geometrytype(geom) = 'MULTIPOLYGON'::text) OR (geom IS NULL))),
    CONSTRAINT enforce_srid_geom CHECK ((public.st_srid(geom) = 4326))
);


ALTER TABLE public.geo_division OWNER TO mapas;

--
-- Name: job; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.job (
    id character varying(255) NOT NULL,
    name character varying(32) NOT NULL,
    iterations integer NOT NULL,
    iterations_count integer NOT NULL,
    interval_string character varying(255) NOT NULL,
    create_timestamp timestamp(0) without time zone NOT NULL,
    next_execution_timestamp timestamp(0) without time zone NOT NULL,
    last_execution_timestamp timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    metadata json NOT NULL,
    status smallint NOT NULL
);


ALTER TABLE public.job OWNER TO mapas;

--
-- Name: COLUMN job.metadata; Type: COMMENT; Schema: public; Owner: mapas
--

COMMENT ON COLUMN public.job.metadata IS '(DC2Type:json)';


--
-- Name: metadata; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.metadata (
    object_id integer NOT NULL,
    object_type public.object_type NOT NULL,
    key character varying(32) NOT NULL,
    value text
);


ALTER TABLE public.metadata OWNER TO mapas;

--
-- Name: metalist_id_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public.metalist_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.metalist_id_seq OWNER TO mapas;

--
-- Name: metalist; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.metalist (
    id integer DEFAULT nextval('public.metalist_id_seq'::regclass) NOT NULL,
    object_type character varying(255) NOT NULL,
    object_id integer NOT NULL,
    grp character varying(32) NOT NULL,
    title character varying(255) NOT NULL,
    description text,
    value text NOT NULL,
    create_timestamp timestamp without time zone DEFAULT now() NOT NULL,
    "order" smallint
);


ALTER TABLE public.metalist OWNER TO mapas;

--
-- Name: notification_id_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public.notification_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.notification_id_seq OWNER TO mapas;

--
-- Name: notification; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.notification (
    id integer DEFAULT nextval('public.notification_id_seq'::regclass) NOT NULL,
    user_id integer NOT NULL,
    request_id integer,
    message text NOT NULL,
    create_timestamp timestamp without time zone DEFAULT now() NOT NULL,
    action_timestamp timestamp without time zone,
    status smallint NOT NULL
);


ALTER TABLE public.notification OWNER TO mapas;

--
-- Name: notification_meta; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.notification_meta (
    id integer NOT NULL,
    object_id integer NOT NULL,
    key character varying(255) NOT NULL,
    value text
);


ALTER TABLE public.notification_meta OWNER TO mapas;

--
-- Name: notification_meta_id_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public.notification_meta_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.notification_meta_id_seq OWNER TO mapas;

--
-- Name: occurrence_id_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public.occurrence_id_seq
    START WITH 100000
    INCREMENT BY 1
    MINVALUE 100000
    NO MAXVALUE
    CACHE 1
    CYCLE;


ALTER TABLE public.occurrence_id_seq OWNER TO mapas;

--
-- Name: opportunity_id_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public.opportunity_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.opportunity_id_seq OWNER TO mapas;

--
-- Name: opportunity; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.opportunity (
    id integer DEFAULT nextval('public.opportunity_id_seq'::regclass) NOT NULL,
    parent_id integer,
    agent_id integer NOT NULL,
    type smallint,
    name character varying(255) NOT NULL,
    short_description text,
    long_description text,
    registration_from timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    registration_to timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    published_registrations boolean NOT NULL,
    registration_categories text,
    create_timestamp timestamp(0) without time zone NOT NULL,
    update_timestamp timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    status smallint NOT NULL,
    subsite_id integer,
    object_type character varying(255) NOT NULL,
    object_id integer NOT NULL,
    avaliable_evaluation_fields json,
    publish_timestamp timestamp without time zone,
    auto_publish boolean DEFAULT false NOT NULL
);


ALTER TABLE public.opportunity OWNER TO mapas;

--
-- Name: opportunity_meta_id_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public.opportunity_meta_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.opportunity_meta_id_seq OWNER TO mapas;

--
-- Name: opportunity_meta; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.opportunity_meta (
    id integer DEFAULT nextval('public.opportunity_meta_id_seq'::regclass) NOT NULL,
    object_id integer NOT NULL,
    key character varying(255) NOT NULL,
    value text
);


ALTER TABLE public.opportunity_meta OWNER TO mapas;

--
-- Name: permission_cache_pending; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.permission_cache_pending (
    id integer NOT NULL,
    object_id integer NOT NULL,
    object_type character varying(255) NOT NULL,
    status smallint DEFAULT 0
);


ALTER TABLE public.permission_cache_pending OWNER TO mapas;

--
-- Name: permission_cache_pending_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public.permission_cache_pending_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.permission_cache_pending_seq OWNER TO mapas;

--
-- Name: procuration; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.procuration (
    token character varying(32) NOT NULL,
    usr_id integer NOT NULL,
    attorney_user_id integer NOT NULL,
    action character varying(255) NOT NULL,
    create_timestamp timestamp(0) without time zone NOT NULL,
    valid_until_timestamp timestamp(0) without time zone DEFAULT NULL::timestamp without time zone
);


ALTER TABLE public.procuration OWNER TO mapas;

--
-- Name: project; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.project (
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
    starts_on timestamp without time zone,
    ends_on timestamp without time zone,
    update_timestamp timestamp(0) without time zone,
    subsite_id integer
);


ALTER TABLE public.project OWNER TO mapas;

--
-- Name: project_event; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.project_event (
    id integer NOT NULL,
    event_id integer NOT NULL,
    project_id integer NOT NULL,
    type smallint NOT NULL,
    status smallint NOT NULL
);


ALTER TABLE public.project_event OWNER TO mapas;

--
-- Name: project_event_id_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public.project_event_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.project_event_id_seq OWNER TO mapas;

--
-- Name: project_event_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: mapas
--

ALTER SEQUENCE public.project_event_id_seq OWNED BY public.project_event.id;


--
-- Name: project_id_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public.project_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.project_id_seq OWNER TO mapas;

--
-- Name: project_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: mapas
--

ALTER SEQUENCE public.project_id_seq OWNED BY public.project.id;


--
-- Name: project_meta; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.project_meta (
    object_id integer NOT NULL,
    key character varying(255) NOT NULL,
    value text,
    id integer NOT NULL
);


ALTER TABLE public.project_meta OWNER TO mapas;

--
-- Name: project_meta_id_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public.project_meta_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.project_meta_id_seq OWNER TO mapas;

--
-- Name: project_meta_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: mapas
--

ALTER SEQUENCE public.project_meta_id_seq OWNED BY public.project_meta.id;


--
-- Name: pseudo_random_id_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public.pseudo_random_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.pseudo_random_id_seq OWNER TO mapas;

--
-- Name: registration_evaluation_id_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public.registration_evaluation_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.registration_evaluation_id_seq OWNER TO mapas;

--
-- Name: registration_field_configuration; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.registration_field_configuration (
    id integer NOT NULL,
    opportunity_id integer,
    title character varying(255) NOT NULL,
    description text,
    categories text,
    required boolean NOT NULL,
    field_type character varying(255) NOT NULL,
    field_options text NOT NULL,
    max_size text,
    display_order smallint DEFAULT 255,
    config text,
    conditional boolean,
    conditional_field character varying(255),
    conditional_value character varying(255)
);


ALTER TABLE public.registration_field_configuration OWNER TO mapas;

--
-- Name: COLUMN registration_field_configuration.categories; Type: COMMENT; Schema: public; Owner: mapas
--

COMMENT ON COLUMN public.registration_field_configuration.categories IS '(DC2Type:array)';


--
-- Name: registration_field_configuration_id_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public.registration_field_configuration_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.registration_field_configuration_id_seq OWNER TO mapas;

--
-- Name: registration_file_configuration; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.registration_file_configuration (
    id integer NOT NULL,
    opportunity_id integer,
    title character varying(255) NOT NULL,
    description text,
    required boolean NOT NULL,
    categories text,
    display_order smallint DEFAULT 255,
    conditional boolean,
    conditional_field character varying(255),
    conditional_value character varying(255)
);


ALTER TABLE public.registration_file_configuration OWNER TO mapas;

--
-- Name: COLUMN registration_file_configuration.categories; Type: COMMENT; Schema: public; Owner: mapas
--

COMMENT ON COLUMN public.registration_file_configuration.categories IS '(DC2Type:array)';


--
-- Name: registration_file_configuration_id_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public.registration_file_configuration_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.registration_file_configuration_id_seq OWNER TO mapas;

--
-- Name: registration_file_configuration_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: mapas
--

ALTER SEQUENCE public.registration_file_configuration_id_seq OWNED BY public.registration_file_configuration.id;


--
-- Name: registration_id_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public.registration_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.registration_id_seq OWNER TO mapas;

--
-- Name: registration_meta; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.registration_meta (
    object_id integer NOT NULL,
    key character varying(255) NOT NULL,
    value text,
    id integer NOT NULL
);


ALTER TABLE public.registration_meta OWNER TO mapas;

--
-- Name: registration_meta_id_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public.registration_meta_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.registration_meta_id_seq OWNER TO mapas;

--
-- Name: registration_meta_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: mapas
--

ALTER SEQUENCE public.registration_meta_id_seq OWNED BY public.registration_meta.id;


--
-- Name: request_id_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public.request_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.request_id_seq OWNER TO mapas;

--
-- Name: request; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.request (
    id integer DEFAULT nextval('public.request_id_seq'::regclass) NOT NULL,
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


ALTER TABLE public.request OWNER TO mapas;

--
-- Name: revision_data_id_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public.revision_data_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.revision_data_id_seq OWNER TO mapas;

--
-- Name: role; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.role (
    id integer NOT NULL,
    usr_id integer,
    name character varying(32) NOT NULL,
    subsite_id integer
);


ALTER TABLE public.role OWNER TO mapas;

--
-- Name: role_id_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public.role_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.role_id_seq OWNER TO mapas;

--
-- Name: role_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: mapas
--

ALTER SEQUENCE public.role_id_seq OWNED BY public.role.id;


--
-- Name: seal; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.seal (
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
    subsite_id integer,
    locked_fields json DEFAULT '[]'::json
);


ALTER TABLE public.seal OWNER TO mapas;

--
-- Name: seal_id_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public.seal_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.seal_id_seq OWNER TO mapas;

--
-- Name: seal_meta; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.seal_meta (
    id integer NOT NULL,
    object_id integer NOT NULL,
    key character varying(255) NOT NULL,
    value text
);


ALTER TABLE public.seal_meta OWNER TO mapas;

--
-- Name: seal_meta_id_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public.seal_meta_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.seal_meta_id_seq OWNER TO mapas;

--
-- Name: seal_relation; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.seal_relation (
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


ALTER TABLE public.seal_relation OWNER TO mapas;

--
-- Name: seal_relation_id_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public.seal_relation_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.seal_relation_id_seq OWNER TO mapas;

--
-- Name: space; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.space (
    id integer NOT NULL,
    parent_id integer,
    location point,
    _geo_location public.geography,
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


ALTER TABLE public.space OWNER TO mapas;

--
-- Name: COLUMN space.location; Type: COMMENT; Schema: public; Owner: mapas
--

COMMENT ON COLUMN public.space.location IS 'type=POINT';


--
-- Name: space_id_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public.space_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.space_id_seq OWNER TO mapas;

--
-- Name: space_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: mapas
--

ALTER SEQUENCE public.space_id_seq OWNED BY public.space.id;


--
-- Name: space_meta; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.space_meta (
    object_id integer NOT NULL,
    key character varying(255) NOT NULL,
    value text,
    id integer NOT NULL
);


ALTER TABLE public.space_meta OWNER TO mapas;

--
-- Name: space_meta_id_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public.space_meta_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.space_meta_id_seq OWNER TO mapas;

--
-- Name: space_meta_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: mapas
--

ALTER SEQUENCE public.space_meta_id_seq OWNED BY public.space_meta.id;


--
-- Name: space_relation; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.space_relation (
    id integer NOT NULL,
    space_id integer,
    object_id integer NOT NULL,
    create_timestamp timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    status smallint,
    object_type character varying(255) NOT NULL
);


ALTER TABLE public.space_relation OWNER TO mapas;

--
-- Name: space_relation_id_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public.space_relation_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.space_relation_id_seq OWNER TO mapas;

--
-- Name: subsite; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.subsite (
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


ALTER TABLE public.subsite OWNER TO mapas;

--
-- Name: subsite_id_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public.subsite_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.subsite_id_seq OWNER TO mapas;

--
-- Name: subsite_meta; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.subsite_meta (
    object_id integer NOT NULL,
    key character varying(255) NOT NULL,
    value text,
    id integer NOT NULL
);


ALTER TABLE public.subsite_meta OWNER TO mapas;

--
-- Name: subsite_meta_id_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public.subsite_meta_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.subsite_meta_id_seq OWNER TO mapas;

--
-- Name: system_role; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.system_role (
    id integer NOT NULL,
    slug character varying(64) NOT NULL,
    name character varying(255) NOT NULL,
    subsite_context boolean NOT NULL,
    permissions json,
    create_timestamp timestamp(0) without time zone NOT NULL,
    update_timestamp timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    status smallint NOT NULL
);


ALTER TABLE public.system_role OWNER TO mapas;

--
-- Name: COLUMN system_role.permissions; Type: COMMENT; Schema: public; Owner: mapas
--

COMMENT ON COLUMN public.system_role.permissions IS '(DC2Type:json)';


--
-- Name: system_role_id_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public.system_role_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.system_role_id_seq OWNER TO mapas;

--
-- Name: term; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.term (
    id integer NOT NULL,
    taxonomy character varying(64) NOT NULL,
    term character varying(255) NOT NULL,
    description text
);


ALTER TABLE public.term OWNER TO mapas;

--
-- Name: COLUMN term.taxonomy; Type: COMMENT; Schema: public; Owner: mapas
--

COMMENT ON COLUMN public.term.taxonomy IS '1=tag';


--
-- Name: term_id_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public.term_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.term_id_seq OWNER TO mapas;

--
-- Name: term_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: mapas
--

ALTER SEQUENCE public.term_id_seq OWNED BY public.term.id;


--
-- Name: term_relation; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.term_relation (
    term_id integer NOT NULL,
    object_type public.object_type NOT NULL,
    object_id integer NOT NULL,
    id integer NOT NULL
);


ALTER TABLE public.term_relation OWNER TO mapas;

--
-- Name: term_relation_id_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public.term_relation_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.term_relation_id_seq OWNER TO mapas;

--
-- Name: term_relation_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: mapas
--

ALTER SEQUENCE public.term_relation_id_seq OWNED BY public.term_relation.id;


--
-- Name: user_app; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.user_app (
    public_key character varying(64) NOT NULL,
    private_key character varying(128) NOT NULL,
    user_id integer NOT NULL,
    name text NOT NULL,
    status integer NOT NULL,
    create_timestamp timestamp without time zone NOT NULL,
    subsite_id integer
);


ALTER TABLE public.user_app OWNER TO mapas;

--
-- Name: user_meta; Type: TABLE; Schema: public; Owner: mapas
--

CREATE TABLE public.user_meta (
    object_id integer NOT NULL,
    key character varying(255) NOT NULL,
    value text,
    id integer NOT NULL
);


ALTER TABLE public.user_meta OWNER TO mapas;

--
-- Name: user_meta_id_seq; Type: SEQUENCE; Schema: public; Owner: mapas
--

CREATE SEQUENCE public.user_meta_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.user_meta_id_seq OWNER TO mapas;

--
-- Name: _mesoregiao gid; Type: DEFAULT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public._mesoregiao ALTER COLUMN gid SET DEFAULT nextval('public._mesoregiao_gid_seq'::regclass);


--
-- Name: _microregiao gid; Type: DEFAULT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public._microregiao ALTER COLUMN gid SET DEFAULT nextval('public._microregiao_gid_seq'::regclass);


--
-- Name: _municipios gid; Type: DEFAULT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public._municipios ALTER COLUMN gid SET DEFAULT nextval('public._municipios_gid_seq'::regclass);


--
-- Name: agent_relation id; Type: DEFAULT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.agent_relation ALTER COLUMN id SET DEFAULT nextval('public.agent_relation_id_seq'::regclass);


--
-- Name: evaluation_method_configuration id; Type: DEFAULT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.evaluation_method_configuration ALTER COLUMN id SET DEFAULT nextval('public.evaluation_method_configuration_id_seq'::regclass);


--
-- Name: event id; Type: DEFAULT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.event ALTER COLUMN id SET DEFAULT nextval('public.event_id_seq'::regclass);


--
-- Name: event_occurrence id; Type: DEFAULT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.event_occurrence ALTER COLUMN id SET DEFAULT nextval('public.event_occurrence_id_seq'::regclass);


--
-- Name: event_occurrence_cancellation id; Type: DEFAULT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.event_occurrence_cancellation ALTER COLUMN id SET DEFAULT nextval('public.event_occurrence_cancellation_id_seq'::regclass);


--
-- Name: event_occurrence_recurrence id; Type: DEFAULT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.event_occurrence_recurrence ALTER COLUMN id SET DEFAULT nextval('public.event_occurrence_recurrence_id_seq'::regclass);


--
-- Name: project id; Type: DEFAULT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.project ALTER COLUMN id SET DEFAULT nextval('public.project_id_seq'::regclass);


--
-- Name: project_event id; Type: DEFAULT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.project_event ALTER COLUMN id SET DEFAULT nextval('public.project_event_id_seq'::regclass);


--
-- Name: space id; Type: DEFAULT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.space ALTER COLUMN id SET DEFAULT nextval('public.space_id_seq'::regclass);


--
-- Name: term id; Type: DEFAULT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.term ALTER COLUMN id SET DEFAULT nextval('public.term_id_seq'::regclass);


--
-- Name: term_relation id; Type: DEFAULT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.term_relation ALTER COLUMN id SET DEFAULT nextval('public.term_relation_id_seq'::regclass);


--
-- Data for Name: _mesoregiao; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public._mesoregiao (gid, id, nm_meso, cd_geocodu, geom) FROM stdin;
\.


--
-- Data for Name: _microregiao; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public._microregiao (gid, id, nm_micro, cd_geocodu, geom) FROM stdin;
\.


--
-- Data for Name: _municipios; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public._municipios (gid, id, cd_geocodm, nm_municip, geom) FROM stdin;
\.


--
-- Data for Name: agent; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.agent (id, parent_id, user_id, type, name, location, _geo_location, short_description, long_description, create_timestamp, status, is_verified, public_location, update_timestamp, subsite_id) FROM stdin;
1	\N	1	1	Admin@local	\N	\N	\N	\N	2019-03-07 00:00:00	1	f	\N	2019-03-07 00:00:00	\N
\.


--
-- Data for Name: agent_meta; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.agent_meta (object_id, key, value, id) FROM stdin;
\.


--
-- Data for Name: agent_relation; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.agent_relation (id, agent_id, object_type, object_id, type, has_control, create_timestamp, status, metadata) FROM stdin;
\.


--
-- Data for Name: chat_message; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.chat_message (id, chat_thread_id, parent_id, user_id, payload, create_timestamp) FROM stdin;
\.


--
-- Data for Name: chat_thread; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.chat_thread (id, object_id, object_type, type, identifier, description, create_timestamp, last_message_timestamp, status) FROM stdin;
\.


--
-- Data for Name: db_update; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.db_update (name, exec_time) FROM stdin;
alter tablel term taxonomy type	2019-03-07 23:54:06.885661
new random id generator	2019-03-07 23:54:06.885661
migrate gender	2019-03-07 23:54:06.885661
create table user apps	2019-03-07 23:54:06.885661
create table user_meta	2019-03-07 23:54:06.885661
create seal and seal relation tables	2019-03-07 23:54:06.885661
resize entity meta key columns	2019-03-07 23:54:06.885661
create registration field configuration table	2019-03-07 23:54:06.885661
alter table registration_file_configuration add categories	2019-03-07 23:54:06.885661
create saas tables	2019-03-07 23:54:06.885661
rename saas tables to subsite	2019-03-07 23:54:06.885661
remove parent_url and add alias_url	2019-03-07 23:54:06.885661
verified seal migration	2019-03-07 23:54:06.885661
create update timestamp entities	2019-03-07 23:54:06.885661
alter table role add column subsite_id	2019-03-07 23:54:06.885661
Fix field options field type from registration field configuration	2019-03-07 23:54:06.885661
ADD columns subsite_id	2019-03-07 23:54:06.885661
remove subsite slug column	2019-03-07 23:54:06.885661
add subsite verified_seals column	2019-03-07 23:54:06.885661
update entities last_update_timestamp with user last log timestamp	2019-03-07 23:54:06.885661
Created owner seal relation field	2019-03-07 23:54:06.885661
create table pcache	2019-03-07 23:54:06.885661
function create pcache id sequence 2	2019-03-07 23:54:06.885661
Add field for maximum size from registration field configuration	2019-03-07 23:54:06.885661
Add notification type for compliant and suggestion messages	2019-03-07 23:54:06.885661
create entity revision tables	2019-03-07 23:54:06.885661
ALTER TABLE file ADD COLUMN path	2019-03-07 23:54:06.885661
*_meta drop all indexes again	2019-03-07 23:54:06.885661
recreate *_meta indexes	2019-03-07 23:54:06.885661
create permission cache pending table2	2019-03-07 23:54:06.885661
create opportunity tables	2019-03-07 23:54:06.885661
DROP CONSTRAINT registration_project_fk");	2019-03-07 23:54:06.885661
fix opportunity parent FK	2019-03-07 23:54:06.885661
fix opportunity type 35	2019-03-07 23:54:06.885661
create opportunity sequence	2019-03-07 23:54:06.885661
update opportunity_meta_id sequence	2019-03-07 23:54:06.885661
rename opportunity_meta key isProjectPhase to isOpportunityPhase	2019-03-07 23:54:06.885661
migrate introInscricoes value to shortDescription	2019-03-07 23:54:06.885661
ALTER TABLE registration ADD consolidated_result	2019-03-07 23:54:06.885661
create evaluation methods tables	2019-03-07 23:54:06.885661
create registration_evaluation table	2019-03-07 23:54:06.885661
ALTER TABLE opportunity ALTER type DROP NOT NULL;	2019-03-07 23:54:06.885661
create seal relation renovation flag field	2019-03-07 23:54:06.885661
create seal relation validate date	2019-03-07 23:54:06.885661
update seal_relation set validate_date	2019-03-07 23:54:06.885661
refactor of entity meta keky value indexes	2019-03-07 23:54:06.885661
DROP index registration_meta_value_idx	2019-03-07 23:54:06.885661
altertable registration_file_and_files_add_order	2019-03-07 23:54:06.885661
replace subsite entidades_habilitadas values	2019-03-07 23:54:06.885661
replace subsite cor entidades values	2019-03-07 23:54:06.885661
ALTER TABLE file ADD private and update	2019-03-07 23:54:06.885661
move private files	2019-03-07 23:54:06.885661
create permission cache sequence	2019-03-07 23:54:06.885661
create evaluation methods sequence	2019-03-07 23:54:06.885661
change opportunity field agent_id not null	2019-03-07 23:54:06.885661
alter table registration add column number	2019-03-07 23:54:06.885661
update registrations set number fixed	2019-03-07 23:54:06.885661
alter table registration add column valuers_exceptions_list	2019-03-07 23:54:06.885661
update taxonomy slug tag	2019-03-07 23:54:06.885661
update taxonomy slug area	2019-03-07 23:54:06.885661
update taxonomy slug linguagem	2019-03-07 23:54:06.885661
recreate pcache	2019-03-07 23:54:19.344941
generate file path	2019-03-07 23:54:19.352266
create entities history entries	2019-03-07 23:54:19.357385
create entities updated revision	2019-03-07 23:54:19.362878
fix update timestamp of revisioned entities	2019-03-07 23:54:19.367904
consolidate registration result	2019-03-07 23:54:19.3728
create avatar thumbs	2019-03-07 23:55:16.963658
CREATE SEQUENCE REGISTRATION SPACE RELATION registration_space_relation_id_seq	2022-12-28 15:18:39.104279
CREATE TABLE spacerelation	2022-12-28 15:18:39.104279
ALTER TABLE registration	2022-12-28 15:18:39.104279
create event attendance table	2022-12-28 15:18:39.104279
create procuration table	2022-12-28 15:18:39.104279
alter table registration_field_configuration add column config	2022-12-28 15:18:39.104279
recreate ALL FKs	2022-12-28 15:18:39.104279
create object_type enum type	2022-12-28 15:18:39.104279
create permission_action enum type	2022-12-28 15:18:39.104279
alter tables to use enum types	2022-12-28 15:18:39.104279
alter table permission_cache_pending add column status	2022-12-28 15:18:39.104279
RECREATE VIEW evaluations AGAIN!	2022-12-28 15:18:39.104279
valuer disabling refactor	2022-12-28 15:18:39.104279
ALTER TABLE metalist ALTER value TYPE TEXT	2022-12-28 15:18:39.104279
Add metadata to Agent Relation	2022-12-28 15:18:39.104279
add timestamp columns to registration_evaluation	2022-12-28 15:18:39.104279
create chat tables	2022-12-28 15:18:39.104279
create table job	2022-12-28 15:18:39.104279
clean existing orphans	2022-12-28 15:18:39.104279
add triggers for orphan cleanup	2022-12-28 15:18:39.104279
Remove lixo angular registration_meta	2022-12-28 15:18:39.104279
Adiciona coluna avaliableEvaluationFields na tabela opportunity	2022-12-28 15:18:39.104279
Consede permissão em todos os campo para todos os avaliadores da oportunidade	2022-12-28 15:18:39.104279
alter seal add column locked_fields	2022-12-28 15:18:39.104279
update taxonomy slug funcao	2022-12-28 15:18:39.104279
create registrations history entries	2022-12-28 15:18:39.949624
create evaluations history entries	2022-12-28 15:18:39.960974
corrige registration_metadada dos campos @	2022-12-28 15:18:39.963793
remove orphan events again	2023-09-16 18:51:34.531169
fix subsite verifiedSeals array	2023-09-16 18:51:34.531169
RECREATE VIEW evaluations AGAIN!!!!!	2023-09-16 18:51:34.531169
adiciona oportunidades na fila de reprocessamento de cache	2023-09-16 18:51:34.531169
adiciona novos indices a tabela agent_relation	2023-09-16 18:51:34.531169
alter job.metadata comment	2023-09-16 18:51:34.531169
Adiciona coluna publish_timestamp na tabela opportunity	2023-09-16 18:51:34.531169
Adiciona coluna auto_publish na tabela opportunity	2023-09-16 18:51:34.531169
Adiciona coluna evaluation_from e evaluation_to na tabela evaluation_method_configuration	2023-09-16 18:51:34.531169
adiciona coluna name na tabela evaluation_method_configuration	2023-09-16 18:51:34.531169
popula as colunas name, evaluation_from e evaluation_to da tabela evaluation_method_configuration	2023-09-16 18:51:34.531169
Renomeia colunas registrationFrom e registrationTo da tabela de projetod	2023-09-16 18:51:34.531169
Adiciona novas coluna na tabela registration_field_configuration	2023-09-16 18:51:34.531169
Adiciona novas coluna na tabela registration_file_configuration	2023-09-16 18:51:34.531169
corrige metadados criados por erro em inscricoes de fases	2023-09-16 18:51:34.531169
Adiciona a coluna description para a descrição da ocorrência	2023-09-16 18:51:34.531169
Adiciona a coluna price para a o valor de entrada da ocorrência	2023-09-16 18:51:34.531169
Adiciona a coluna priceInfo para a informações sobre o valor de entrada da ocorrência	2023-09-16 18:51:34.531169
Apaga registro do db-update de "Definição dos cammpos cpf e cnpj com base no documento" para que rode novamente	2023-09-16 18:51:34.531169
Corrige config dos campos na entidade registration_fields_configurarion	2023-09-16 18:51:34.531169
seta como vazio campo escolaridade do agent caso esteja com valor não informado	2023-09-16 18:51:34.531169
altera tipo da coluna description na tabela file	2023-09-16 18:51:34.531169
faz com que o updateTimestamp seja igual ao createTimestamp na criacão da entidade	2023-09-16 18:51:34.531169
migra valores das colunas do tipo array para do tipo json	2023-09-16 18:51:34.531169
define metadado isDataCollection = 0 nas fases sem campos configurados	2023-09-16 18:51:34.531169
create table system_role	2023-09-16 18:51:34.531169
alter system_role.permissions comment	2023-09-16 18:51:34.531169
Definição dos cammpos cpf e cnpj com base no documento	2023-09-16 18:51:35.638149
Atualiza os campos das ocorrencias para o novo padrao	2023-09-16 18:51:35.644497
create permission cache for users	2023-09-16 18:51:35.648676
Atualiza campos condicionados para funcionar na nova estrutura	2023-09-16 18:51:35.653105
criando fases de resultado final para as oportunidades existentes sem fase final	2023-09-16 18:51:35.657056
\.


--
-- Data for Name: entity_revision; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.entity_revision (id, user_id, object_id, object_type, create_timestamp, action, message) FROM stdin;
1	1	1	MapasCulturais\\Entities\\Agent	2019-03-07 00:00:00	created	Registro criado.
\.


--
-- Data for Name: entity_revision_data; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.entity_revision_data (id, "timestamp", key, value) FROM stdin;
1	2019-03-07 23:54:19	_type	1
2	2019-03-07 23:54:19	name	"Admin@local"
3	2019-03-07 23:54:19	publicLocation	null
4	2019-03-07 23:54:19	location	{"latitude":0,"longitude":0}
5	2019-03-07 23:54:19	shortDescription	null
6	2019-03-07 23:54:19	longDescription	null
7	2019-03-07 23:54:19	createTimestamp	{"date":"2019-03-07 00:00:00.000000","timezone_type":3,"timezone":"UTC"}
8	2019-03-07 23:54:19	status	1
9	2019-03-07 23:54:19	updateTimestamp	{"date":"2019-03-07 00:00:00.000000","timezone_type":3,"timezone":"UTC"}
10	2019-03-07 23:54:19	_subsiteId	null
\.


--
-- Data for Name: entity_revision_revision_data; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.entity_revision_revision_data (revision_id, revision_data_id) FROM stdin;
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
\.


--
-- Data for Name: evaluation_method_configuration; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.evaluation_method_configuration (id, opportunity_id, type, evaluation_from, evaluation_to, name) FROM stdin;
\.


--
-- Data for Name: evaluationmethodconfiguration_meta; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.evaluationmethodconfiguration_meta (id, object_id, key, value) FROM stdin;
\.


--
-- Data for Name: event; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.event (id, project_id, name, short_description, long_description, rules, create_timestamp, status, agent_id, is_verified, type, update_timestamp, subsite_id) FROM stdin;
\.


--
-- Data for Name: event_attendance; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.event_attendance (id, user_id, event_occurrence_id, event_id, space_id, type, reccurrence_string, start_timestamp, end_timestamp, create_timestamp) FROM stdin;
\.


--
-- Data for Name: event_meta; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.event_meta (key, object_id, value, id) FROM stdin;
\.


--
-- Data for Name: event_occurrence; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.event_occurrence (id, space_id, event_id, rule, starts_on, ends_on, starts_at, ends_at, frequency, separation, count, until, timezone_name, status, description, price, priceinfo) FROM stdin;
\.


--
-- Data for Name: event_occurrence_cancellation; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.event_occurrence_cancellation (id, event_occurrence_id, date) FROM stdin;
\.


--
-- Data for Name: event_occurrence_recurrence; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.event_occurrence_recurrence (id, event_occurrence_id, month, day, week) FROM stdin;
\.


--
-- Data for Name: file; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.file (id, md5, mime_type, name, object_type, object_id, create_timestamp, grp, description, parent_id, path, private) FROM stdin;
\.


--
-- Data for Name: geo_division; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.geo_division (id, parent_id, type, cod, name, geom) FROM stdin;
\.


--
-- Data for Name: job; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.job (id, name, iterations, iterations_count, interval_string, create_timestamp, next_execution_timestamp, last_execution_timestamp, metadata, status) FROM stdin;
\.


--
-- Data for Name: metadata; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.metadata (object_id, object_type, key, value) FROM stdin;
\.


--
-- Data for Name: metalist; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.metalist (id, object_type, object_id, grp, title, description, value, create_timestamp, "order") FROM stdin;
\.


--
-- Data for Name: notification; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.notification (id, user_id, request_id, message, create_timestamp, action_timestamp, status) FROM stdin;
\.


--
-- Data for Name: notification_meta; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.notification_meta (id, object_id, key, value) FROM stdin;
\.


--
-- Data for Name: opportunity; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.opportunity (id, parent_id, agent_id, type, name, short_description, long_description, registration_from, registration_to, published_registrations, registration_categories, create_timestamp, update_timestamp, status, subsite_id, object_type, object_id, avaliable_evaluation_fields, publish_timestamp, auto_publish) FROM stdin;
\.


--
-- Data for Name: opportunity_meta; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.opportunity_meta (id, object_id, key, value) FROM stdin;
\.


--
-- Data for Name: pcache; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.pcache (id, user_id, action, create_timestamp, object_type, object_id) FROM stdin;
\.


--
-- Data for Name: permission_cache_pending; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.permission_cache_pending (id, object_id, object_type, status) FROM stdin;
\.


--
-- Data for Name: procuration; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.procuration (token, usr_id, attorney_user_id, action, create_timestamp, valid_until_timestamp) FROM stdin;
\.


--
-- Data for Name: project; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.project (id, name, short_description, long_description, create_timestamp, status, agent_id, is_verified, type, parent_id, starts_on, ends_on, update_timestamp, subsite_id) FROM stdin;
\.


--
-- Data for Name: project_event; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.project_event (id, event_id, project_id, type, status) FROM stdin;
\.


--
-- Data for Name: project_meta; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.project_meta (object_id, key, value, id) FROM stdin;
\.


--
-- Data for Name: registration; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.registration (id, opportunity_id, category, agent_id, create_timestamp, sent_timestamp, status, agents_data, subsite_id, consolidated_result, number, valuers_exceptions_list, space_data) FROM stdin;
\.


--
-- Data for Name: registration_evaluation; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.registration_evaluation (id, registration_id, user_id, result, evaluation_data, status, create_timestamp, update_timestamp) FROM stdin;
\.


--
-- Data for Name: registration_field_configuration; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.registration_field_configuration (id, opportunity_id, title, description, categories, required, field_type, field_options, max_size, display_order, config, conditional, conditional_field, conditional_value) FROM stdin;
\.


--
-- Data for Name: registration_file_configuration; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.registration_file_configuration (id, opportunity_id, title, description, required, categories, display_order, conditional, conditional_field, conditional_value) FROM stdin;
\.


--
-- Data for Name: registration_meta; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.registration_meta (object_id, key, value, id) FROM stdin;
\.


--
-- Data for Name: request; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.request (id, request_uid, requester_user_id, origin_type, origin_id, destination_type, destination_id, metadata, type, create_timestamp, action_timestamp, status) FROM stdin;
\.


--
-- Data for Name: role; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.role (id, usr_id, name, subsite_id) FROM stdin;
2	1	saasSuperAdmin	\N
\.


--
-- Data for Name: seal; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.seal (id, agent_id, name, short_description, long_description, valid_period, create_timestamp, status, certificate_text, update_timestamp, subsite_id, locked_fields) FROM stdin;
1	1	Selo Mapas	Descrição curta Selo Mapas	Descrição longa Selo Mapas	0	2019-03-07 23:54:04	1	\N	2019-03-07 00:00:00	\N	[]
\.


--
-- Data for Name: seal_meta; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.seal_meta (id, object_id, key, value) FROM stdin;
\.


--
-- Data for Name: seal_relation; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.seal_relation (id, seal_id, object_id, create_timestamp, status, object_type, agent_id, owner_id, validate_date, renovation_request) FROM stdin;
\.


--
-- Data for Name: space; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.space (id, parent_id, location, _geo_location, name, short_description, long_description, create_timestamp, status, type, agent_id, is_verified, public, update_timestamp, subsite_id) FROM stdin;
\.


--
-- Data for Name: space_meta; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.space_meta (object_id, key, value, id) FROM stdin;
\.


--
-- Data for Name: space_relation; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.space_relation (id, space_id, object_id, create_timestamp, status, object_type) FROM stdin;
\.


--
-- Data for Name: spatial_ref_sys; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.spatial_ref_sys (srid, auth_name, auth_srid, srtext, proj4text) FROM stdin;
\.


--
-- Data for Name: subsite; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.subsite (id, name, create_timestamp, status, agent_id, url, namespace, alias_url, verified_seals) FROM stdin;
\.


--
-- Data for Name: subsite_meta; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.subsite_meta (object_id, key, value, id) FROM stdin;
\.


--
-- Data for Name: system_role; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.system_role (id, slug, name, subsite_context, permissions, create_timestamp, update_timestamp, status) FROM stdin;
\.


--
-- Data for Name: term; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.term (id, taxonomy, term, description) FROM stdin;
\.


--
-- Data for Name: term_relation; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.term_relation (term_id, object_type, object_id, id) FROM stdin;
\.


--
-- Data for Name: user_app; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.user_app (public_key, private_key, user_id, name, status, create_timestamp, subsite_id) FROM stdin;
\.


--
-- Data for Name: user_meta; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.user_meta (object_id, key, value, id) FROM stdin;
1	deleteAccountToken	54cd760dfca39f8e8c8332d7499bdb44f921dc16	1
1	localAuthenticationPassword	$2y$10$iIXeqhX.4fEAAVZPsbtRde7CFw1ChduCi8NsnXGnJc6TlelY6gf3e	2
\.


--
-- Data for Name: usr; Type: TABLE DATA; Schema: public; Owner: mapas
--

COPY public.usr (id, auth_provider, auth_uid, email, last_login_timestamp, create_timestamp, status, profile_id) FROM stdin;
1	1	1	Admin@local	2019-03-08 19:03:34	2019-03-07 00:00:00	1	1
\.


--
-- Data for Name: geocode_settings; Type: TABLE DATA; Schema: tiger; Owner: mapas
--

COPY tiger.geocode_settings (name, setting, unit, category, short_desc) FROM stdin;
\.


--
-- Data for Name: pagc_gaz; Type: TABLE DATA; Schema: tiger; Owner: mapas
--

COPY tiger.pagc_gaz (id, seq, word, stdword, token, is_custom) FROM stdin;
\.


--
-- Data for Name: pagc_lex; Type: TABLE DATA; Schema: tiger; Owner: mapas
--

COPY tiger.pagc_lex (id, seq, word, stdword, token, is_custom) FROM stdin;
\.


--
-- Data for Name: pagc_rules; Type: TABLE DATA; Schema: tiger; Owner: mapas
--

COPY tiger.pagc_rules (id, rule, is_custom) FROM stdin;
\.


--
-- Data for Name: topology; Type: TABLE DATA; Schema: topology; Owner: mapas
--

COPY topology.topology (id, name, srid, "precision", hasz) FROM stdin;
\.


--
-- Data for Name: layer; Type: TABLE DATA; Schema: topology; Owner: mapas
--

COPY topology.layer (topology_id, layer_id, schema_name, table_name, feature_column, feature_type, level, child_id) FROM stdin;
\.


--
-- Name: _mesoregiao_gid_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public._mesoregiao_gid_seq', 1, false);


--
-- Name: _microregiao_gid_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public._microregiao_gid_seq', 1, false);


--
-- Name: _municipios_gid_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public._municipios_gid_seq', 1, false);


--
-- Name: agent_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public.agent_id_seq', 1, true);


--
-- Name: agent_meta_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public.agent_meta_id_seq', 1, false);


--
-- Name: agent_relation_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public.agent_relation_id_seq', 1, false);


--
-- Name: chat_message_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public.chat_message_id_seq', 1, false);


--
-- Name: chat_thread_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public.chat_thread_id_seq', 1, false);


--
-- Name: entity_revision_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public.entity_revision_id_seq', 1, true);


--
-- Name: evaluation_method_configuration_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public.evaluation_method_configuration_id_seq', 1, false);


--
-- Name: evaluationmethodconfiguration_meta_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public.evaluationmethodconfiguration_meta_id_seq', 1, false);


--
-- Name: event_attendance_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public.event_attendance_id_seq', 1, false);


--
-- Name: event_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public.event_id_seq', 1, false);


--
-- Name: event_meta_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public.event_meta_id_seq', 1, false);


--
-- Name: event_occurrence_cancellation_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public.event_occurrence_cancellation_id_seq', 1, false);


--
-- Name: event_occurrence_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public.event_occurrence_id_seq', 1, false);


--
-- Name: event_occurrence_recurrence_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public.event_occurrence_recurrence_id_seq', 1, false);


--
-- Name: file_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public.file_id_seq', 1, false);


--
-- Name: geo_division_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public.geo_division_id_seq', 1, false);


--
-- Name: metalist_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public.metalist_id_seq', 1, false);


--
-- Name: notification_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public.notification_id_seq', 1, false);


--
-- Name: notification_meta_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public.notification_meta_id_seq', 1, false);


--
-- Name: occurrence_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public.occurrence_id_seq', 100000, false);


--
-- Name: opportunity_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public.opportunity_id_seq', 1, false);


--
-- Name: opportunity_meta_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public.opportunity_meta_id_seq', 1, false);


--
-- Name: pcache_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public.pcache_id_seq', 1, false);


--
-- Name: permission_cache_pending_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public.permission_cache_pending_seq', 1, false);


--
-- Name: project_event_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public.project_event_id_seq', 1, false);


--
-- Name: project_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public.project_id_seq', 1, false);


--
-- Name: project_meta_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public.project_meta_id_seq', 1, false);


--
-- Name: pseudo_random_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public.pseudo_random_id_seq', 1, false);


--
-- Name: registration_evaluation_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public.registration_evaluation_id_seq', 1, false);


--
-- Name: registration_field_configuration_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public.registration_field_configuration_id_seq', 1, false);


--
-- Name: registration_file_configuration_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public.registration_file_configuration_id_seq', 1, false);


--
-- Name: registration_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public.registration_id_seq', 1, false);


--
-- Name: registration_meta_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public.registration_meta_id_seq', 1, false);


--
-- Name: request_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public.request_id_seq', 1, false);


--
-- Name: revision_data_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public.revision_data_id_seq', 10, true);


--
-- Name: role_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public.role_id_seq', 2, true);


--
-- Name: seal_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public.seal_id_seq', 1, false);


--
-- Name: seal_meta_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public.seal_meta_id_seq', 1, false);


--
-- Name: seal_relation_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public.seal_relation_id_seq', 1, false);


--
-- Name: space_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public.space_id_seq', 1, false);


--
-- Name: space_meta_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public.space_meta_id_seq', 1, false);


--
-- Name: space_relation_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public.space_relation_id_seq', 1, false);


--
-- Name: subsite_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public.subsite_id_seq', 1, false);


--
-- Name: subsite_meta_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public.subsite_meta_id_seq', 1, false);


--
-- Name: system_role_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public.system_role_id_seq', 1, false);


--
-- Name: term_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public.term_id_seq', 1, false);


--
-- Name: term_relation_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public.term_relation_id_seq', 1, false);


--
-- Name: user_meta_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public.user_meta_id_seq', 2, true);


--
-- Name: usr_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapas
--

SELECT pg_catalog.setval('public.usr_id_seq', 1, true);


--
-- Name: topology_id_seq; Type: SEQUENCE SET; Schema: topology; Owner: mapas
--

SELECT pg_catalog.setval('topology.topology_id_seq', 1, false);


--
-- Name: _mesoregiao _mesoregiao_pkey; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public._mesoregiao
    ADD CONSTRAINT _mesoregiao_pkey PRIMARY KEY (gid);


--
-- Name: _microregiao _microregiao_pkey; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public._microregiao
    ADD CONSTRAINT _microregiao_pkey PRIMARY KEY (gid);


--
-- Name: _municipios _municipios_pkey; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public._municipios
    ADD CONSTRAINT _municipios_pkey PRIMARY KEY (gid);


--
-- Name: agent_meta agent_meta_pk; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.agent_meta
    ADD CONSTRAINT agent_meta_pk PRIMARY KEY (id);


--
-- Name: agent agent_pk; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.agent
    ADD CONSTRAINT agent_pk PRIMARY KEY (id);


--
-- Name: agent_relation agent_relation_pkey; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.agent_relation
    ADD CONSTRAINT agent_relation_pkey PRIMARY KEY (id);


--
-- Name: chat_message chat_message_pkey; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.chat_message
    ADD CONSTRAINT chat_message_pkey PRIMARY KEY (id);


--
-- Name: chat_thread chat_thread_pkey; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.chat_thread
    ADD CONSTRAINT chat_thread_pkey PRIMARY KEY (id);


--
-- Name: db_update db_update_pk; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.db_update
    ADD CONSTRAINT db_update_pk PRIMARY KEY (name);


--
-- Name: entity_revision_data entity_revision_data_pkey; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.entity_revision_data
    ADD CONSTRAINT entity_revision_data_pkey PRIMARY KEY (id);


--
-- Name: entity_revision entity_revision_pkey; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.entity_revision
    ADD CONSTRAINT entity_revision_pkey PRIMARY KEY (id);


--
-- Name: entity_revision_revision_data entity_revision_revision_data_pkey; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.entity_revision_revision_data
    ADD CONSTRAINT entity_revision_revision_data_pkey PRIMARY KEY (revision_id, revision_data_id);


--
-- Name: evaluation_method_configuration evaluation_method_configuration_pkey; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.evaluation_method_configuration
    ADD CONSTRAINT evaluation_method_configuration_pkey PRIMARY KEY (id);


--
-- Name: evaluationmethodconfiguration_meta evaluationmethodconfiguration_meta_pkey; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.evaluationmethodconfiguration_meta
    ADD CONSTRAINT evaluationmethodconfiguration_meta_pkey PRIMARY KEY (id);


--
-- Name: event_attendance event_attendance_pkey; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.event_attendance
    ADD CONSTRAINT event_attendance_pkey PRIMARY KEY (id);


--
-- Name: event_meta event_meta_pk; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.event_meta
    ADD CONSTRAINT event_meta_pk PRIMARY KEY (id);


--
-- Name: event_occurrence_cancellation event_occurrence_cancellation_pkey; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.event_occurrence_cancellation
    ADD CONSTRAINT event_occurrence_cancellation_pkey PRIMARY KEY (id);


--
-- Name: event_occurrence event_occurrence_pkey; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.event_occurrence
    ADD CONSTRAINT event_occurrence_pkey PRIMARY KEY (id);


--
-- Name: event_occurrence_recurrence event_occurrence_recurrence_pkey; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.event_occurrence_recurrence
    ADD CONSTRAINT event_occurrence_recurrence_pkey PRIMARY KEY (id);


--
-- Name: event event_pk; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.event
    ADD CONSTRAINT event_pk PRIMARY KEY (id);


--
-- Name: file file_pk; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.file
    ADD CONSTRAINT file_pk PRIMARY KEY (id);


--
-- Name: geo_division geo_divisions_pkey; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.geo_division
    ADD CONSTRAINT geo_divisions_pkey PRIMARY KEY (id);


--
-- Name: job job_pkey; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.job
    ADD CONSTRAINT job_pkey PRIMARY KEY (id);


--
-- Name: metadata metadata_pk; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.metadata
    ADD CONSTRAINT metadata_pk PRIMARY KEY (object_id, object_type, key);


--
-- Name: metalist metalist_pk; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.metalist
    ADD CONSTRAINT metalist_pk PRIMARY KEY (id);


--
-- Name: notification_meta notification_meta_pkey; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.notification_meta
    ADD CONSTRAINT notification_meta_pkey PRIMARY KEY (id);


--
-- Name: notification notification_pk; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.notification
    ADD CONSTRAINT notification_pk PRIMARY KEY (id);


--
-- Name: opportunity_meta opportunity_meta_pkey; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.opportunity_meta
    ADD CONSTRAINT opportunity_meta_pkey PRIMARY KEY (id);


--
-- Name: opportunity opportunity_pkey; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.opportunity
    ADD CONSTRAINT opportunity_pkey PRIMARY KEY (id);


--
-- Name: pcache pcache_pkey; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.pcache
    ADD CONSTRAINT pcache_pkey PRIMARY KEY (id);


--
-- Name: permission_cache_pending permission_cache_pending_pkey; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.permission_cache_pending
    ADD CONSTRAINT permission_cache_pending_pkey PRIMARY KEY (id);


--
-- Name: procuration procuration_pkey; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.procuration
    ADD CONSTRAINT procuration_pkey PRIMARY KEY (token);


--
-- Name: project_event project_event_pk; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.project_event
    ADD CONSTRAINT project_event_pk PRIMARY KEY (id);


--
-- Name: project_meta project_meta_pk; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.project_meta
    ADD CONSTRAINT project_meta_pk PRIMARY KEY (id);


--
-- Name: project project_pk; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.project
    ADD CONSTRAINT project_pk PRIMARY KEY (id);


--
-- Name: registration_evaluation registration_evaluation_pkey; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.registration_evaluation
    ADD CONSTRAINT registration_evaluation_pkey PRIMARY KEY (id);


--
-- Name: registration_field_configuration registration_field_configuration_pkey; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.registration_field_configuration
    ADD CONSTRAINT registration_field_configuration_pkey PRIMARY KEY (id);


--
-- Name: registration_file_configuration registration_file_configuration_pkey; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.registration_file_configuration
    ADD CONSTRAINT registration_file_configuration_pkey PRIMARY KEY (id);


--
-- Name: registration_meta registration_meta_pk; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.registration_meta
    ADD CONSTRAINT registration_meta_pk PRIMARY KEY (id);


--
-- Name: registration registration_pkey; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.registration
    ADD CONSTRAINT registration_pkey PRIMARY KEY (id);


--
-- Name: request request_pk; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.request
    ADD CONSTRAINT request_pk PRIMARY KEY (id);


--
-- Name: role role_pk; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.role
    ADD CONSTRAINT role_pk PRIMARY KEY (id);


--
-- Name: subsite saas_pkey; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.subsite
    ADD CONSTRAINT saas_pkey PRIMARY KEY (id);


--
-- Name: seal_meta seal_meta_pkey; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.seal_meta
    ADD CONSTRAINT seal_meta_pkey PRIMARY KEY (id);


--
-- Name: seal seal_pkey; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.seal
    ADD CONSTRAINT seal_pkey PRIMARY KEY (id);


--
-- Name: seal_relation seal_relation_pkey; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.seal_relation
    ADD CONSTRAINT seal_relation_pkey PRIMARY KEY (id);


--
-- Name: space_meta space_meta_pk; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.space_meta
    ADD CONSTRAINT space_meta_pk PRIMARY KEY (id);


--
-- Name: space space_pk; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.space
    ADD CONSTRAINT space_pk PRIMARY KEY (id);


--
-- Name: space_relation space_relation_pkey; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.space_relation
    ADD CONSTRAINT space_relation_pkey PRIMARY KEY (id);


--
-- Name: subsite_meta subsite_meta_pkey; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.subsite_meta
    ADD CONSTRAINT subsite_meta_pkey PRIMARY KEY (id);


--
-- Name: system_role system_role_pkey; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.system_role
    ADD CONSTRAINT system_role_pkey PRIMARY KEY (id);


--
-- Name: term term_pk; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.term
    ADD CONSTRAINT term_pk PRIMARY KEY (id);


--
-- Name: term_relation term_relation_pk; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.term_relation
    ADD CONSTRAINT term_relation_pk PRIMARY KEY (id);


--
-- Name: user_app user_app_pk; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.user_app
    ADD CONSTRAINT user_app_pk PRIMARY KEY (public_key);


--
-- Name: user_meta user_meta_pkey; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.user_meta
    ADD CONSTRAINT user_meta_pkey PRIMARY KEY (id);


--
-- Name: usr usr_pk; Type: CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.usr
    ADD CONSTRAINT usr_pk PRIMARY KEY (id);


--
-- Name: agent_meta_key_idx; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX agent_meta_key_idx ON public.agent_meta USING btree (key);


--
-- Name: agent_meta_owner_idx; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX agent_meta_owner_idx ON public.agent_meta USING btree (object_id);


--
-- Name: agent_meta_owner_key_idx; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX agent_meta_owner_key_idx ON public.agent_meta USING btree (object_id, key);


--
-- Name: agent_relation_has_control; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX agent_relation_has_control ON public.agent_relation USING btree (has_control);


--
-- Name: agent_relation_owner; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX agent_relation_owner ON public.agent_relation USING btree (object_type, object_id);


--
-- Name: agent_relation_owner_agent; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX agent_relation_owner_agent ON public.agent_relation USING btree (object_type, object_id, agent_id);


--
-- Name: agent_relation_owner_id; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX agent_relation_owner_id ON public.agent_relation USING btree (object_id);


--
-- Name: agent_relation_owner_type; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX agent_relation_owner_type ON public.agent_relation USING btree (object_type);


--
-- Name: agent_relation_status; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX agent_relation_status ON public.agent_relation USING btree (status);


--
-- Name: alias_url_index; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX alias_url_index ON public.subsite USING btree (alias_url);


--
-- Name: evaluationmethodconfiguration_meta_owner_idx; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX evaluationmethodconfiguration_meta_owner_idx ON public.evaluationmethodconfiguration_meta USING btree (object_id);


--
-- Name: evaluationmethodconfiguration_meta_owner_key_idx; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX evaluationmethodconfiguration_meta_owner_key_idx ON public.evaluationmethodconfiguration_meta USING btree (object_id, key);


--
-- Name: event_attendance_type_idx; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX event_attendance_type_idx ON public.event_attendance USING btree (type);


--
-- Name: event_meta_key_idx; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX event_meta_key_idx ON public.event_meta USING btree (key);


--
-- Name: event_meta_owner_idx; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX event_meta_owner_idx ON public.event_meta USING btree (object_id);


--
-- Name: event_meta_owner_key_idx; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX event_meta_owner_key_idx ON public.event_meta USING btree (object_id, key);


--
-- Name: event_occurrence_status_index; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX event_occurrence_status_index ON public.event_occurrence USING btree (status);


--
-- Name: file_group_index; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX file_group_index ON public.file USING btree (grp);


--
-- Name: file_owner_grp_index; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX file_owner_grp_index ON public.file USING btree (object_type, object_id, grp);


--
-- Name: file_owner_index; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX file_owner_index ON public.file USING btree (object_type, object_id);


--
-- Name: geo_divisions_geom_idx; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX geo_divisions_geom_idx ON public.geo_division USING gist (geom);


--
-- Name: idx_1a0e9a30232d562b; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX idx_1a0e9a30232d562b ON public.space_relation USING btree (object_id);


--
-- Name: idx_1a0e9a3023575340; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX idx_1a0e9a3023575340 ON public.space_relation USING btree (space_id);


--
-- Name: idx_209c792e9a34590f; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX idx_209c792e9a34590f ON public.registration_file_configuration USING btree (opportunity_id);


--
-- Name: idx_22781144c79c849a; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX idx_22781144c79c849a ON public.user_app USING btree (subsite_id);


--
-- Name: idx_268b9c9dc79c849a; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX idx_268b9c9dc79c849a ON public.agent USING btree (subsite_id);


--
-- Name: idx_2972c13ac79c849a; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX idx_2972c13ac79c849a ON public.space USING btree (subsite_id);


--
-- Name: idx_2e186c5c833d8f43; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX idx_2e186c5c833d8f43 ON public.registration_evaluation USING btree (registration_id);


--
-- Name: idx_2e186c5ca76ed395; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX idx_2e186c5ca76ed395 ON public.registration_evaluation USING btree (user_id);


--
-- Name: idx_2e30ae30c79c849a; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX idx_2e30ae30c79c849a ON public.seal USING btree (subsite_id);


--
-- Name: idx_2fb3d0eec79c849a; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX idx_2fb3d0eec79c849a ON public.project USING btree (subsite_id);


--
-- Name: idx_350dd4be140e9f00; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX idx_350dd4be140e9f00 ON public.event_attendance USING btree (event_occurrence_id);


--
-- Name: idx_350dd4be23575340; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX idx_350dd4be23575340 ON public.event_attendance USING btree (space_id);


--
-- Name: idx_350dd4be71f7e88b; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX idx_350dd4be71f7e88b ON public.event_attendance USING btree (event_id);


--
-- Name: idx_350dd4bea76ed395; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX idx_350dd4bea76ed395 ON public.event_attendance USING btree (user_id);


--
-- Name: idx_3bae0aa7c79c849a; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX idx_3bae0aa7c79c849a ON public.event USING btree (subsite_id);


--
-- Name: idx_3d853098232d562b; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX idx_3d853098232d562b ON public.pcache USING btree (object_id);


--
-- Name: idx_3d853098a76ed395; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX idx_3d853098a76ed395 ON public.pcache USING btree (user_id);


--
-- Name: idx_57698a6ac79c849a; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX idx_57698a6ac79c849a ON public.role USING btree (subsite_id);


--
-- Name: idx_60c85cb1166d1f9c; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX idx_60c85cb1166d1f9c ON public.registration_field_configuration USING btree (opportunity_id);


--
-- Name: idx_60c85cb19a34590f; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX idx_60c85cb19a34590f ON public.registration_field_configuration USING btree (opportunity_id);


--
-- Name: idx_62a8a7a73414710b; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX idx_62a8a7a73414710b ON public.registration USING btree (agent_id);


--
-- Name: idx_62a8a7a79a34590f; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX idx_62a8a7a79a34590f ON public.registration USING btree (opportunity_id);


--
-- Name: idx_62a8a7a7c79c849a; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX idx_62a8a7a7c79c849a ON public.registration USING btree (subsite_id);


--
-- Name: idx_fab3fc16727aca70; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX idx_fab3fc16727aca70 ON public.chat_message USING btree (parent_id);


--
-- Name: idx_fab3fc16a76ed395; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX idx_fab3fc16a76ed395 ON public.chat_message USING btree (user_id);


--
-- Name: idx_fab3fc16c47d5262; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX idx_fab3fc16c47d5262 ON public.chat_message USING btree (chat_thread_id);


--
-- Name: job_next_execution_timestamp_idx; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX job_next_execution_timestamp_idx ON public.job USING btree (next_execution_timestamp);


--
-- Name: job_search_idx; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX job_search_idx ON public.job USING btree (next_execution_timestamp, iterations_count, status);


--
-- Name: notification_meta_key_idx; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX notification_meta_key_idx ON public.notification_meta USING btree (key);


--
-- Name: notification_meta_owner_idx; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX notification_meta_owner_idx ON public.notification_meta USING btree (object_id);


--
-- Name: notification_meta_owner_key_idx; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX notification_meta_owner_key_idx ON public.notification_meta USING btree (object_id, key);


--
-- Name: opportunity_entity_idx; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX opportunity_entity_idx ON public.opportunity USING btree (object_type, object_id);


--
-- Name: opportunity_meta_owner_idx; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX opportunity_meta_owner_idx ON public.opportunity_meta USING btree (object_id);


--
-- Name: opportunity_meta_owner_key_idx; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX opportunity_meta_owner_key_idx ON public.opportunity_meta USING btree (object_id, key);


--
-- Name: opportunity_owner_idx; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX opportunity_owner_idx ON public.opportunity USING btree (agent_id);


--
-- Name: opportunity_parent_idx; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX opportunity_parent_idx ON public.opportunity USING btree (parent_id);


--
-- Name: owner_index; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX owner_index ON public.term_relation USING btree (object_type, object_id);


--
-- Name: pcache_owner_idx; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX pcache_owner_idx ON public.pcache USING btree (object_type, object_id);


--
-- Name: pcache_permission_idx; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX pcache_permission_idx ON public.pcache USING btree (object_type, object_id, action);


--
-- Name: pcache_permission_user_idx; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX pcache_permission_user_idx ON public.pcache USING btree (object_type, object_id, action, user_id);


--
-- Name: procuration_attorney_idx; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX procuration_attorney_idx ON public.procuration USING btree (attorney_user_id);


--
-- Name: procuration_usr_idx; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX procuration_usr_idx ON public.procuration USING btree (usr_id);


--
-- Name: project_meta_key_idx; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX project_meta_key_idx ON public.project_meta USING btree (key);


--
-- Name: project_meta_owner_idx; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX project_meta_owner_idx ON public.project_meta USING btree (object_id);


--
-- Name: project_meta_owner_key_idx; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX project_meta_owner_key_idx ON public.project_meta USING btree (object_id, key);


--
-- Name: registration_meta_owner_idx; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX registration_meta_owner_idx ON public.registration_meta USING btree (object_id);


--
-- Name: registration_meta_owner_key_idx; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX registration_meta_owner_key_idx ON public.registration_meta USING btree (object_id, key);


--
-- Name: request_uid; Type: INDEX; Schema: public; Owner: mapas
--

CREATE UNIQUE INDEX request_uid ON public.request USING btree (request_uid);


--
-- Name: requester_user_index; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX requester_user_index ON public.request USING btree (requester_user_id, origin_type, origin_id);


--
-- Name: seal_meta_key_idx; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX seal_meta_key_idx ON public.seal_meta USING btree (key);


--
-- Name: seal_meta_owner_idx; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX seal_meta_owner_idx ON public.seal_meta USING btree (object_id);


--
-- Name: seal_meta_owner_key_idx; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX seal_meta_owner_key_idx ON public.seal_meta USING btree (object_id, key);


--
-- Name: space_location; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX space_location ON public.space USING gist (_geo_location);


--
-- Name: space_meta_key_idx; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX space_meta_key_idx ON public.space_meta USING btree (key);


--
-- Name: space_meta_owner_idx; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX space_meta_owner_idx ON public.space_meta USING btree (object_id);


--
-- Name: space_meta_owner_key_idx; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX space_meta_owner_key_idx ON public.space_meta USING btree (object_id, key);


--
-- Name: space_type; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX space_type ON public.space USING btree (type);


--
-- Name: subsite_meta_key_idx; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX subsite_meta_key_idx ON public.subsite_meta USING btree (key);


--
-- Name: subsite_meta_owner_idx; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX subsite_meta_owner_idx ON public.subsite_meta USING btree (object_id);


--
-- Name: subsite_meta_owner_key_idx; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX subsite_meta_owner_key_idx ON public.subsite_meta USING btree (object_id, key);


--
-- Name: taxonomy_term_unique; Type: INDEX; Schema: public; Owner: mapas
--

CREATE UNIQUE INDEX taxonomy_term_unique ON public.term USING btree (taxonomy, term);


--
-- Name: uniq_330cb54c9a34590f; Type: INDEX; Schema: public; Owner: mapas
--

CREATE UNIQUE INDEX uniq_330cb54c9a34590f ON public.evaluation_method_configuration USING btree (opportunity_id);


--
-- Name: url_index; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX url_index ON public.subsite USING btree (url);


--
-- Name: user_meta_key_idx; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX user_meta_key_idx ON public.user_meta USING btree (key);


--
-- Name: user_meta_owner_idx; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX user_meta_owner_idx ON public.user_meta USING btree (object_id);


--
-- Name: user_meta_owner_key_idx; Type: INDEX; Schema: public; Owner: mapas
--

CREATE INDEX user_meta_owner_key_idx ON public.user_meta USING btree (object_id, key);


--
-- Name: agent trigger_clean_orphans_agent; Type: TRIGGER; Schema: public; Owner: mapas
--

CREATE TRIGGER trigger_clean_orphans_agent AFTER DELETE ON public.agent FOR EACH ROW EXECUTE FUNCTION public.fn_clean_orphans('MapasCulturais\Entities\Agent');


--
-- Name: chat_message trigger_clean_orphans_chat_message; Type: TRIGGER; Schema: public; Owner: mapas
--

CREATE TRIGGER trigger_clean_orphans_chat_message AFTER DELETE ON public.chat_message FOR EACH ROW EXECUTE FUNCTION public.fn_clean_orphans('MapasCulturais\Entities\ChatMessage');


--
-- Name: chat_thread trigger_clean_orphans_chat_thread; Type: TRIGGER; Schema: public; Owner: mapas
--

CREATE TRIGGER trigger_clean_orphans_chat_thread AFTER DELETE ON public.chat_thread FOR EACH ROW EXECUTE FUNCTION public.fn_clean_orphans('MapasCulturais\Entities\ChatThread');


--
-- Name: evaluation_method_configuration trigger_clean_orphans_evaluation_method_configuration; Type: TRIGGER; Schema: public; Owner: mapas
--

CREATE TRIGGER trigger_clean_orphans_evaluation_method_configuration AFTER DELETE ON public.evaluation_method_configuration FOR EACH ROW EXECUTE FUNCTION public.fn_clean_orphans('MapasCulturais\Entities\EvaluationMethodConfiguration');


--
-- Name: event trigger_clean_orphans_event; Type: TRIGGER; Schema: public; Owner: mapas
--

CREATE TRIGGER trigger_clean_orphans_event AFTER DELETE ON public.event FOR EACH ROW EXECUTE FUNCTION public.fn_clean_orphans('MapasCulturais\Entities\Event');


--
-- Name: notification trigger_clean_orphans_notification; Type: TRIGGER; Schema: public; Owner: mapas
--

CREATE TRIGGER trigger_clean_orphans_notification AFTER DELETE ON public.notification FOR EACH ROW EXECUTE FUNCTION public.fn_clean_orphans('MapasCulturais\Entities\Notification');


--
-- Name: opportunity trigger_clean_orphans_opportunity; Type: TRIGGER; Schema: public; Owner: mapas
--

CREATE TRIGGER trigger_clean_orphans_opportunity AFTER DELETE ON public.opportunity FOR EACH ROW EXECUTE FUNCTION public.fn_clean_orphans('MapasCulturais\Entities\Opportunity');


--
-- Name: project trigger_clean_orphans_project; Type: TRIGGER; Schema: public; Owner: mapas
--

CREATE TRIGGER trigger_clean_orphans_project AFTER DELETE ON public.project FOR EACH ROW EXECUTE FUNCTION public.fn_clean_orphans('MapasCulturais\Entities\Project');


--
-- Name: registration trigger_clean_orphans_registration; Type: TRIGGER; Schema: public; Owner: mapas
--

CREATE TRIGGER trigger_clean_orphans_registration AFTER DELETE ON public.registration FOR EACH ROW EXECUTE FUNCTION public.fn_clean_orphans('MapasCulturais\Entities\Registration');


--
-- Name: registration_file_configuration trigger_clean_orphans_registration_file_configuration; Type: TRIGGER; Schema: public; Owner: mapas
--

CREATE TRIGGER trigger_clean_orphans_registration_file_configuration AFTER DELETE ON public.registration_file_configuration FOR EACH ROW EXECUTE FUNCTION public.fn_clean_orphans('MapasCulturais\Entities\RegistrationFileConfiguration');


--
-- Name: space trigger_clean_orphans_space; Type: TRIGGER; Schema: public; Owner: mapas
--

CREATE TRIGGER trigger_clean_orphans_space AFTER DELETE ON public.space FOR EACH ROW EXECUTE FUNCTION public.fn_clean_orphans('MapasCulturais\Entities\Space');


--
-- Name: subsite trigger_clean_orphans_subsite; Type: TRIGGER; Schema: public; Owner: mapas
--

CREATE TRIGGER trigger_clean_orphans_subsite AFTER DELETE ON public.subsite FOR EACH ROW EXECUTE FUNCTION public.fn_clean_orphans('MapasCulturais\Entities\Subsite');


--
-- Name: usr fk_1762498cccfa12b8; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.usr
    ADD CONSTRAINT fk_1762498cccfa12b8 FOREIGN KEY (profile_id) REFERENCES public.agent(id) ON DELETE SET NULL;


--
-- Name: registration_meta fk_18cc03e9232d562b; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.registration_meta
    ADD CONSTRAINT fk_18cc03e9232d562b FOREIGN KEY (object_id) REFERENCES public.registration(id) ON DELETE CASCADE;


--
-- Name: space_relation fk_1a0e9a30232d562b; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.space_relation
    ADD CONSTRAINT fk_1a0e9a30232d562b FOREIGN KEY (object_id) REFERENCES public.registration(id) ON DELETE CASCADE;


--
-- Name: space_relation fk_1a0e9a3023575340; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.space_relation
    ADD CONSTRAINT fk_1a0e9a3023575340 FOREIGN KEY (space_id) REFERENCES public.space(id) ON DELETE CASCADE;


--
-- Name: registration_file_configuration fk_209c792e9a34590f; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.registration_file_configuration
    ADD CONSTRAINT fk_209c792e9a34590f FOREIGN KEY (opportunity_id) REFERENCES public.opportunity(id) ON DELETE CASCADE;


--
-- Name: user_app fk_22781144a76ed395; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.user_app
    ADD CONSTRAINT fk_22781144a76ed395 FOREIGN KEY (user_id) REFERENCES public.usr(id) ON DELETE CASCADE;


--
-- Name: user_app fk_22781144bddfbe89; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.user_app
    ADD CONSTRAINT fk_22781144bddfbe89 FOREIGN KEY (subsite_id) REFERENCES public.subsite(id) ON DELETE CASCADE;


--
-- Name: agent fk_268b9c9d727aca70; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.agent
    ADD CONSTRAINT fk_268b9c9d727aca70 FOREIGN KEY (parent_id) REFERENCES public.agent(id) ON DELETE SET NULL;


--
-- Name: agent fk_268b9c9da76ed395; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.agent
    ADD CONSTRAINT fk_268b9c9da76ed395 FOREIGN KEY (user_id) REFERENCES public.usr(id) ON DELETE CASCADE;


--
-- Name: agent fk_268b9c9dbddfbe89; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.agent
    ADD CONSTRAINT fk_268b9c9dbddfbe89 FOREIGN KEY (subsite_id) REFERENCES public.subsite(id) ON DELETE SET NULL;


--
-- Name: space fk_2972c13a3414710b; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.space
    ADD CONSTRAINT fk_2972c13a3414710b FOREIGN KEY (agent_id) REFERENCES public.agent(id) ON DELETE CASCADE;


--
-- Name: space fk_2972c13a727aca70; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.space
    ADD CONSTRAINT fk_2972c13a727aca70 FOREIGN KEY (parent_id) REFERENCES public.space(id) ON DELETE CASCADE;


--
-- Name: space fk_2972c13abddfbe89; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.space
    ADD CONSTRAINT fk_2972c13abddfbe89 FOREIGN KEY (subsite_id) REFERENCES public.subsite(id) ON DELETE SET NULL;


--
-- Name: opportunity_meta fk_2bb06d08232d562b; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.opportunity_meta
    ADD CONSTRAINT fk_2bb06d08232d562b FOREIGN KEY (object_id) REFERENCES public.opportunity(id) ON DELETE CASCADE;


--
-- Name: registration_evaluation fk_2e186c5c833d8f43; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.registration_evaluation
    ADD CONSTRAINT fk_2e186c5c833d8f43 FOREIGN KEY (registration_id) REFERENCES public.registration(id) ON DELETE CASCADE;


--
-- Name: registration_evaluation fk_2e186c5ca76ed395; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.registration_evaluation
    ADD CONSTRAINT fk_2e186c5ca76ed395 FOREIGN KEY (user_id) REFERENCES public.usr(id) ON DELETE CASCADE;


--
-- Name: seal fk_2e30ae303414710b; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.seal
    ADD CONSTRAINT fk_2e30ae303414710b FOREIGN KEY (agent_id) REFERENCES public.agent(id) ON DELETE CASCADE;


--
-- Name: seal fk_2e30ae30bddfbe89; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.seal
    ADD CONSTRAINT fk_2e30ae30bddfbe89 FOREIGN KEY (subsite_id) REFERENCES public.subsite(id) ON DELETE CASCADE;


--
-- Name: project fk_2fb3d0ee3414710b; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.project
    ADD CONSTRAINT fk_2fb3d0ee3414710b FOREIGN KEY (agent_id) REFERENCES public.agent(id) ON DELETE CASCADE;


--
-- Name: project fk_2fb3d0ee727aca70; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.project
    ADD CONSTRAINT fk_2fb3d0ee727aca70 FOREIGN KEY (parent_id) REFERENCES public.project(id) ON DELETE CASCADE;


--
-- Name: project fk_2fb3d0eebddfbe89; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.project
    ADD CONSTRAINT fk_2fb3d0eebddfbe89 FOREIGN KEY (subsite_id) REFERENCES public.subsite(id) ON DELETE SET NULL;


--
-- Name: evaluation_method_configuration fk_330cb54c9a34590f; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.evaluation_method_configuration
    ADD CONSTRAINT fk_330cb54c9a34590f FOREIGN KEY (opportunity_id) REFERENCES public.opportunity(id) ON DELETE CASCADE;


--
-- Name: event_attendance fk_350dd4be140e9f00; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.event_attendance
    ADD CONSTRAINT fk_350dd4be140e9f00 FOREIGN KEY (event_occurrence_id) REFERENCES public.event_occurrence(id) ON DELETE CASCADE;


--
-- Name: event_attendance fk_350dd4be23575340; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.event_attendance
    ADD CONSTRAINT fk_350dd4be23575340 FOREIGN KEY (space_id) REFERENCES public.space(id) ON DELETE CASCADE;


--
-- Name: event_attendance fk_350dd4be71f7e88b; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.event_attendance
    ADD CONSTRAINT fk_350dd4be71f7e88b FOREIGN KEY (event_id) REFERENCES public.event(id) ON DELETE CASCADE;


--
-- Name: event_attendance fk_350dd4bea76ed395; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.event_attendance
    ADD CONSTRAINT fk_350dd4bea76ed395 FOREIGN KEY (user_id) REFERENCES public.usr(id) ON DELETE CASCADE;


--
-- Name: event_occurrence_recurrence fk_388eccb140e9f00; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.event_occurrence_recurrence
    ADD CONSTRAINT fk_388eccb140e9f00 FOREIGN KEY (event_occurrence_id) REFERENCES public.event_occurrence(id) ON DELETE CASCADE;


--
-- Name: request fk_3b978f9fba78f12a; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.request
    ADD CONSTRAINT fk_3b978f9fba78f12a FOREIGN KEY (requester_user_id) REFERENCES public.usr(id) ON DELETE CASCADE;


--
-- Name: event fk_3bae0aa7166d1f9c; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.event
    ADD CONSTRAINT fk_3bae0aa7166d1f9c FOREIGN KEY (project_id) REFERENCES public.project(id) ON DELETE SET NULL;


--
-- Name: event fk_3bae0aa73414710b; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.event
    ADD CONSTRAINT fk_3bae0aa73414710b FOREIGN KEY (agent_id) REFERENCES public.agent(id) ON DELETE CASCADE;


--
-- Name: event fk_3bae0aa7bddfbe89; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.event
    ADD CONSTRAINT fk_3bae0aa7bddfbe89 FOREIGN KEY (subsite_id) REFERENCES public.subsite(id) ON DELETE SET NULL;


--
-- Name: pcache fk_3d853098a76ed395; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.pcache
    ADD CONSTRAINT fk_3d853098a76ed395 FOREIGN KEY (user_id) REFERENCES public.usr(id) ON DELETE CASCADE;


--
-- Name: seal_relation fk_487af6513414710b; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.seal_relation
    ADD CONSTRAINT fk_487af6513414710b FOREIGN KEY (agent_id) REFERENCES public.agent(id) ON DELETE CASCADE;


--
-- Name: seal_relation fk_487af65154778145; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.seal_relation
    ADD CONSTRAINT fk_487af65154778145 FOREIGN KEY (seal_id) REFERENCES public.seal(id) ON DELETE CASCADE;


--
-- Name: seal_relation fk_487af6517e3c61f9; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.seal_relation
    ADD CONSTRAINT fk_487af6517e3c61f9 FOREIGN KEY (owner_id) REFERENCES public.agent(id) ON DELETE CASCADE;


--
-- Name: agent_relation fk_54585edd3414710b; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.agent_relation
    ADD CONSTRAINT fk_54585edd3414710b FOREIGN KEY (agent_id) REFERENCES public.agent(id) ON DELETE CASCADE;


--
-- Name: role fk_57698a6abddfbe89; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.role
    ADD CONSTRAINT fk_57698a6abddfbe89 FOREIGN KEY (subsite_id) REFERENCES public.subsite(id) ON DELETE CASCADE;


--
-- Name: role fk_57698a6ac69d3fb; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.role
    ADD CONSTRAINT fk_57698a6ac69d3fb FOREIGN KEY (usr_id) REFERENCES public.usr(id) ON DELETE CASCADE;


--
-- Name: registration_field_configuration fk_60c85cb19a34590f; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.registration_field_configuration
    ADD CONSTRAINT fk_60c85cb19a34590f FOREIGN KEY (opportunity_id) REFERENCES public.opportunity(id) ON DELETE CASCADE;


--
-- Name: registration fk_62a8a7a73414710b; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.registration
    ADD CONSTRAINT fk_62a8a7a73414710b FOREIGN KEY (agent_id) REFERENCES public.agent(id) ON DELETE CASCADE;


--
-- Name: registration fk_62a8a7a79a34590f; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.registration
    ADD CONSTRAINT fk_62a8a7a79a34590f FOREIGN KEY (opportunity_id) REFERENCES public.opportunity(id) ON DELETE CASCADE;


--
-- Name: registration fk_62a8a7a7bddfbe89; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.registration
    ADD CONSTRAINT fk_62a8a7a7bddfbe89 FOREIGN KEY (subsite_id) REFERENCES public.subsite(id) ON DELETE SET NULL;


--
-- Name: notification_meta fk_6fce5f0f232d562b; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.notification_meta
    ADD CONSTRAINT fk_6fce5f0f232d562b FOREIGN KEY (object_id) REFERENCES public.notification(id) ON DELETE CASCADE;


--
-- Name: subsite_meta fk_780702f5232d562b; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.subsite_meta
    ADD CONSTRAINT fk_780702f5232d562b FOREIGN KEY (object_id) REFERENCES public.subsite(id) ON DELETE CASCADE;


--
-- Name: agent_meta fk_7a69aed6232d562b; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.agent_meta
    ADD CONSTRAINT fk_7a69aed6232d562b FOREIGN KEY (object_id) REFERENCES public.agent(id) ON DELETE CASCADE;


--
-- Name: opportunity fk_8389c3d73414710b; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.opportunity
    ADD CONSTRAINT fk_8389c3d73414710b FOREIGN KEY (agent_id) REFERENCES public.agent(id) ON DELETE CASCADE;


--
-- Name: opportunity fk_8389c3d7727aca70; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.opportunity
    ADD CONSTRAINT fk_8389c3d7727aca70 FOREIGN KEY (parent_id) REFERENCES public.opportunity(id) ON DELETE CASCADE;


--
-- Name: file fk_8c9f3610727aca70; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.file
    ADD CONSTRAINT fk_8c9f3610727aca70 FOREIGN KEY (parent_id) REFERENCES public.file(id) ON DELETE CASCADE;


--
-- Name: entity_revision_revision_data fk_9977a8521dfa7c8f; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.entity_revision_revision_data
    ADD CONSTRAINT fk_9977a8521dfa7c8f FOREIGN KEY (revision_id) REFERENCES public.entity_revision(id) ON DELETE CASCADE;


--
-- Name: entity_revision_revision_data fk_9977a852b4906f58; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.entity_revision_revision_data
    ADD CONSTRAINT fk_9977a852b4906f58 FOREIGN KEY (revision_data_id) REFERENCES public.entity_revision_data(id) ON DELETE CASCADE;


--
-- Name: event_occurrence_cancellation fk_a5506736140e9f00; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.event_occurrence_cancellation
    ADD CONSTRAINT fk_a5506736140e9f00 FOREIGN KEY (event_occurrence_id) REFERENCES public.event_occurrence(id) ON DELETE CASCADE;


--
-- Name: seal_meta fk_a92e5e22232d562b; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.seal_meta
    ADD CONSTRAINT fk_a92e5e22232d562b FOREIGN KEY (object_id) REFERENCES public.seal(id) ON DELETE CASCADE;


--
-- Name: user_meta fk_ad7358fc232d562b; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.user_meta
    ADD CONSTRAINT fk_ad7358fc232d562b FOREIGN KEY (object_id) REFERENCES public.usr(id) ON DELETE CASCADE;


--
-- Name: subsite fk_b0f67b6f3414710b; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.subsite
    ADD CONSTRAINT fk_b0f67b6f3414710b FOREIGN KEY (agent_id) REFERENCES public.agent(id);


--
-- Name: space_meta fk_bc846ebf232d562b; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.space_meta
    ADD CONSTRAINT fk_bc846ebf232d562b FOREIGN KEY (object_id) REFERENCES public.space(id) ON DELETE CASCADE;


--
-- Name: notification fk_bf5476ca427eb8a5; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.notification
    ADD CONSTRAINT fk_bf5476ca427eb8a5 FOREIGN KEY (request_id) REFERENCES public.request(id) ON DELETE CASCADE;


--
-- Name: notification fk_bf5476caa76ed395; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.notification
    ADD CONSTRAINT fk_bf5476caa76ed395 FOREIGN KEY (user_id) REFERENCES public.usr(id) ON DELETE CASCADE;


--
-- Name: event_meta fk_c839589e232d562b; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.event_meta
    ADD CONSTRAINT fk_c839589e232d562b FOREIGN KEY (object_id) REFERENCES public.event(id) ON DELETE CASCADE;


--
-- Name: entity_revision fk_cf97a98ca76ed395; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.entity_revision
    ADD CONSTRAINT fk_cf97a98ca76ed395 FOREIGN KEY (user_id) REFERENCES public.usr(id) ON DELETE CASCADE;


--
-- Name: procuration fk_d7bae7f3aeb2ed7; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.procuration
    ADD CONSTRAINT fk_d7bae7f3aeb2ed7 FOREIGN KEY (attorney_user_id) REFERENCES public.usr(id) ON DELETE CASCADE;


--
-- Name: procuration fk_d7bae7fc69d3fb; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.procuration
    ADD CONSTRAINT fk_d7bae7fc69d3fb FOREIGN KEY (usr_id) REFERENCES public.usr(id) ON DELETE CASCADE;


--
-- Name: evaluationmethodconfiguration_meta fk_d7edf8b2232d562b; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.evaluationmethodconfiguration_meta
    ADD CONSTRAINT fk_d7edf8b2232d562b FOREIGN KEY (object_id) REFERENCES public.evaluation_method_configuration(id) ON DELETE CASCADE;


--
-- Name: event_occurrence fk_e61358dc23575340; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.event_occurrence
    ADD CONSTRAINT fk_e61358dc23575340 FOREIGN KEY (space_id) REFERENCES public.space(id) ON DELETE CASCADE;


--
-- Name: event_occurrence fk_e61358dc71f7e88b; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.event_occurrence
    ADD CONSTRAINT fk_e61358dc71f7e88b FOREIGN KEY (event_id) REFERENCES public.event(id) ON DELETE CASCADE;


--
-- Name: term_relation fk_eddf39fde2c35fc; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.term_relation
    ADD CONSTRAINT fk_eddf39fde2c35fc FOREIGN KEY (term_id) REFERENCES public.term(id) ON DELETE CASCADE;


--
-- Name: project_meta fk_ee63dc2d232d562b; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.project_meta
    ADD CONSTRAINT fk_ee63dc2d232d562b FOREIGN KEY (object_id) REFERENCES public.project(id) ON DELETE CASCADE;


--
-- Name: chat_message fk_fab3fc16727aca70; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.chat_message
    ADD CONSTRAINT fk_fab3fc16727aca70 FOREIGN KEY (parent_id) REFERENCES public.chat_message(id) ON DELETE CASCADE;


--
-- Name: chat_message fk_fab3fc16a76ed395; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.chat_message
    ADD CONSTRAINT fk_fab3fc16a76ed395 FOREIGN KEY (user_id) REFERENCES public.usr(id) ON DELETE CASCADE;


--
-- Name: chat_message fk_fab3fc16c47d5262; Type: FK CONSTRAINT; Schema: public; Owner: mapas
--

ALTER TABLE ONLY public.chat_message
    ADD CONSTRAINT fk_fab3fc16c47d5262 FOREIGN KEY (chat_thread_id) REFERENCES public.chat_thread(id) ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

