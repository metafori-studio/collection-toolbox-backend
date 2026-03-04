{config}: ''
  server:
    http_listen_port: ${config.tempoPort}
    log_level: info

  distributor:
    receivers:
      otlp:
        protocols:
          grpc:
            endpoint: "0.0.0.0:${config.tempoOtlpGrpcPort}"
          http:
            endpoint: "0.0.0.0:${config.tempoOtlpHttpPort}"

  ingester:
    lifecycler:
      ring:
        kvstore:
          store: inmemory
        replication_factor: 1
    max_block_duration: 5m

  compactor:
    ring:
      kvstore:
        store: inmemory

  storage:
    trace:
      backend: local
      wal:
        path: ${config.tempoDataDir}/wal
      local:
        path: ${config.tempoDataDir}/blocks

  metrics_generator:
    registry:
      external_labels:
        source: tempo
    storage:
      path: ${config.tempoDataDir}/generator/wal
      remote_write:
        - url: http://127.0.0.1:${config.prometheusPort}/api/v1/write
    ring:
      kvstore:
        store: inmemory

  overrides:
    metrics_generator_processors: [service-graphs, span-metrics, local-blocks]
''
