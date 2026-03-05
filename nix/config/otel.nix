{ config }:
''
  receivers:
    otlp:
      protocols:
        grpc:
          endpoint: 0.0.0.0:${config.otelOtlpGrpcPort}
        http:
          endpoint: 0.0.0.0:${config.otelOtlpHttpPort}

  processors:
    batch:
      send_batch_size: 1000
      timeout: 1s

  exporters:
    debug:
      verbosity: detailed
    prometheus:
      endpoint: "0.0.0.0:${config.otelMetricsPort}"
    otlp/tempo:
      endpoint: "127.0.0.1:${config.tempoOtlpGrpcPort}"
      tls:
        insecure: true
    loki:
      endpoint: "http://127.0.0.1:${config.lokiPort}/loki/api/v1/push"
      default_labels_enabled:
        exporter: true
        job: true

  extensions:
    health_check:
      endpoint: 127.0.0.1:${config.otelHealthCheckPort}
    pprof:
      endpoint: 127.0.0.1:${config.otelPprofPort}
    zpages:
      endpoint: 127.0.0.1:${config.otelZpagesPort}

  service:
    extensions: [health_check, pprof, zpages]
    pipelines:
      traces:
        receivers: [otlp]
        processors: [batch]
        exporters: [debug, otlp/tempo]
      metrics:
        receivers: [otlp]
        processors: [batch]
        exporters: [debug, prometheus]
      logs:
        receivers: [otlp]
        processors: [batch]
        exporters: [debug, loki]
''
