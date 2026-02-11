{{/*
Expand the name of the chart.
*/}}
{{- define "mapas.name" -}}
{{- default .Chart.Name .Values.nameOverride | trunc 63 | trimSuffix "-" }}
{{- end }}

{{/*
Create a default fully qualified app name.
*/}}
{{- define "mapas.fullname" -}}
{{- if .Values.fullnameOverride }}
{{- .Values.fullnameOverride | trunc 63 | trimSuffix "-" }}
{{- else }}
{{- $name := default .Chart.Name .Values.nameOverride }}
{{- if contains $name .Release.Name }}
{{- .Release.Name | trunc 63 | trimSuffix "-" }}
{{- else }}
{{- printf "%s-%s" .Release.Name $name | trunc 63 | trimSuffix "-" }}
{{- end }}
{{- end }}
{{- end }}

{{/*
Return ingress hostname based on namespace
Uses mapas.ingress.host as base domain (e.g., mapas.tec.br)
*/}}
{{- define "mapas.ingress.host" -}}
{{- if .Values.ingress.hosts }}
  {{- range .Values.ingress.hosts }}
    {{- if .host }}
      {{- /* Use explicit host if defined */}}
      {{- .host }}
    {{- end }}
  {{- end }}
{{- else }}
  {{- /* Fallback: dynamic host based on namespace */}}
  {{- $baseDomain := .Values.ingress.baseDomain | default "mapas.tec.br" }}
  {{- $subdomain := .Release.Namespace }}
  {{- printf "%s.%s" $subdomain $baseDomain }}
{{- end }}
{{- end }}

{{/*
Create chart name and version as used by the chart label.
*/}}
{{- define "mapas.chart" -}}
{{- printf "%s-%s" .Chart.Name .Chart.Version | replace "+" "_" | trunc 63 | trimSuffix "-" }}
{{- end }}

{{/*
Common labels
*/}}
{{- define "mapas.labels" -}}
helm.sh/chart: {{ include "mapas.chart" . }}
{{ include "mapas.selectorLabels" . }}
{{- if .Chart.AppVersion }}
app.kubernetes.io/version: {{ .Chart.AppVersion | quote }}
{{- end }}
app.kubernetes.io/managed-by: {{ .Release.Service }}
{{- end }}

{{/*
Selector labels
*/}}
{{- define "mapas.selectorLabels" -}}
app.kubernetes.io/name: {{ include "mapas.name" . }}
app.kubernetes.io/instance: {{ .Release.Name }}
{{- end }}

{{/*
Create the name of the service account to use
*/}}
{{- define "mapas.serviceAccountName" -}}
{{- if .Values.serviceAccount.create }}
{{- default (include "mapas.fullname" .) .Values.serviceAccount.name }}
{{- else }}
{{- default "default" .Values.serviceAccount.name }}
{{- end }}
{{- end }}

{{/*
Return the PostgreSQL hostname
*/}}
{{- define "mapas.postgresql.host" -}}
{{- if .Values.postgresql.enabled }}
{{- printf "%s-postgresql" (include "mapas.fullname" .) }}
{{- else }}
{{- .Values.mapas.database.host }}
{{- end }}
{{- end }}

{{/*
Return the PostgreSQL port
*/}}
{{- define "mapas.postgresql.port" -}}
{{- if .Values.postgresql.enabled }}
{{- printf "5432" }}
{{- else }}
{{- .Values.mapas.database.port | default "5432" }}
{{- end }}
{{- end }}

{{/*
Return the PostgreSQL database name (groundhog2k postgres chart)
*/}}
{{- define "mapas.postgresql.database" -}}
{{- if .Values.postgresql.enabled }}
{{- .Values.postgresql.userDatabase.name }}
{{- else }}
{{- .Values.mapas.database.name }}
{{- end }}
{{- end }}

{{/*
Return the PostgreSQL username (groundhog2k postgres chart)
*/}}
{{- define "mapas.postgresql.username" -}}
{{- if .Values.postgresql.enabled }}
{{- .Values.postgresql.settings.superuser | default "mapas" }}
{{- else }}
{{- .Values.mapas.database.user }}
{{- end }}
{{- end }}

{{/*
Return the PostgreSQL secret name (groundhog2k postgres chart uses fullname for secret)
*/}}
{{- define "mapas.postgresql.secretName" -}}
{{- if .Values.postgresql.enabled }}
{{- printf "%s-postgresql" (include "mapas.fullname" .) }}
{{- else }}
{{- if .Values.mapas.database.existingSecret }}
{{- .Values.mapas.database.existingSecret }}
{{- else }}
{{- printf "%s-db-secret" (include "mapas.fullname" .) }}
{{- end }}
{{- end }}
{{- end }}

{{/*
Return the Redis cache hostname (PHP Redis connect uses default port 6379)
*/}}
{{- define "mapas.redis.cache.host" -}}
{{- if index .Values "redis-cache" "enabled" }}
{{- printf "%s-redis-cache" (include "mapas.fullname" .) }}
{{- else }}
{{- .Values.mapas.redisCache.host }}
{{- end }}
{{- end }}

{{/*
Return the Redis cache hostname with port
*/}}
{{- define "mapas.redis.cache.port" -}}
{{- if index .Values "redis-cache" "enabled" }}
{{- "6379" }}
{{- else }}
{{- .Values.mapas.redisCache.port | default "6379" | toString }}
{{- end }}
{{- end }}

{{/*
Return to Redis cache hostname with port
*/}}
{{- define "mapas.redis.cache.hostWithPort" -}}
{{- $host := include "mapas.redis.cache.host" . }}
{{- if $host }}
{{- $port := .Values.mapas.redisCache.port | default "6379" | toString }}
{{- printf "%s:%s" $host $port }}
{{- else }}
{{- "" }}
{{- end }}
{{- end }}

{{/*
Return to Redis sessions hostname (without port)
*/}}
{{- define "mapas.redis.sessions.host" -}}
{{- if index .Values "redis-sessions" "enabled" }}
{{- printf "%s-redis-sessions" (include "mapas.fullname" .) }}
{{- else if .Values.mapas.useSameRedisForCacheAndSessions }}
{{- include "mapas.redis.cache.host" . }}
{{- else if .Values.mapas.redisSessions.host }}
{{- .Values.mapas.redisSessions.host }}
{{- else }}
{{- "" }}
{{- end }}
{{- end }}

{{/*
Return the Redis sessions port
*/}}
{{- define "mapas.redis.sessions.port" -}}
{{- if index .Values "redis-sessions" "enabled" }}
{{- "6379" }}
{{- else if .Values.mapas.useSameRedisForCacheAndSessions }}
{{- .Values.mapas.redisCache.port | default "6379" }}
{{- else if .Values.mapas.redisSessions.port }}
{{- .Values.mapas.redisSessions.port }}
{{- else }}
{{- "6379" }}
{{- end }}
{{- end }}

{{/*
Return the Redis sessions password (empty if no password)
*/}}
{{- define "mapas.redis.sessions.password" -}}
{{- if .Values.mapas.useSameRedisForCacheAndSessions }}
{{- if .Values.mapas.redisCache.existingSecret }}
{{- "" }} {{/* Password will come from secret in deployment */}}
{{- else if .Values.mapas.redisCache.password }}
{{- .Values.mapas.redisCache.password }}
{{- else }}
{{- "" }}
{{- end }}
{{- else if .Values.mapas.redisSessions.existingSecret }}
{{- "" }} {{/* Password will come from secret in deployment */}}
{{- else if .Values.mapas.redisSessions.password }}
{{- .Values.mapas.redisSessions.password }}
{{- else }}
{{- "" }}
{{- end }}
{{- end }}

{{/*
Return the Redis sessions connection string (host:port)
*/}}
{{- define "mapas.redis.sessions.connection" -}}
{{- $host := include "mapas.redis.sessions.host" . }}
{{- $port := include "mapas.redis.sessions.port" . }}
{{- if and $host $port }}
{{- printf "%s:%s" $host $port }}
{{- else }}
{{- "" }}
{{- end }}
{{- end }}

{{/*
Return the sessions save path
*/}}
{{- define "mapas.sessions.savePath" -}}
{{- $connection := "" }}
{{- $password := "" }}
{{- $queryParams := "" }}

{{- /* Determine connection string */}}
{{- if index .Values "redis-sessions" "enabled" }}
  {{- $connection = printf "tcp://%s-redis-sessions:6379" (include "mapas.fullname" .) }}
  {{- /* Check if subchart has auth enabled */}}
  {{- if index .Values "redis-sessions" "auth" "enabled" }}
    {{- if index .Values "redis-sessions" "auth" "password" }}
      {{- $password = index .Values "redis-sessions" "auth" "password" }}
    {{- end }}
  {{- end }}
{{- else if .Values.mapas.useSameRedisForCacheAndSessions }}
  {{- $host := include "mapas.redis.cache.host" . }}
  {{- $port := .Values.mapas.redisCache.port | default 6379 | int }}
  {{- $connection = printf "tcp://%s:%d" $host $port }}
  {{- if and (not .Values.mapas.redisCache.existingSecret) .Values.mapas.redisCache.password }}
    {{- $password = .Values.mapas.redisCache.password }}
  {{- end }}
{{- else if .Values.mapas.redisSessions.host }}
  {{- $host := .Values.mapas.redisSessions.host }}
  {{- $port := .Values.mapas.redisSessions.port | default 6379 | int }}
  {{- $connection = printf "tcp://%s:%d" $host $port }}
  {{- if and (not .Values.mapas.redisSessions.existingSecret) .Values.mapas.redisSessions.password }}
    {{- $password = .Values.mapas.redisSessions.password }}
  {{- end }}
{{- else }}
  {{- $connection = .Values.mapas.sessions.savePath }}
{{- end }}

{{- /* Add auth parameter if password is available */}}
{{- if $password }}
  {{- $connection = printf "%s?auth=%s" $connection $password }}
{{- end }}

{{- $connection }}
{{- end }}
