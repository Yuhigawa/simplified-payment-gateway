# Artillery Load Testing

This repository contains Artillery load tests for a transaction system, split into light-load (functional validation) and heavy-load (performance & scalability).

The goal is to:

Validate correctness under normal traffic

Stress the system with millions of transactions

Measure throughput, latency, and stability

## 📁 Test Types Overview
Test	Purpose	Traffic Level
Light Load	Functional + sanity testing	Low
Heavy Load	Performance & scalability testing	Very High

### 🟢 Light Load Test
- ### 🎯 Purpose

  Verify endpoints work correctly

  Validate user creation and transactions

  Catch functional or logical errors

  Safe to run locally during development

- ## 📄 File
  ```
  light-load-test.yml
  ```

- ## 🚦 Load Profile (example)
  ```
  duration: ~2–3 minutes
  arrivalRate: 5 → 50 rps
  ```

- ## 📊 Expected Numbers
  | Metric                    | Approximate Value |
  | ------------------------- | ----------------- |
  | Requests per second (RPS) | 5–50              |
  | Total requests            | 3k–6k             |
  | Concurrent users          | < 100             |
  | CPU usage                 | Low               |
  | DB contention             | Minimal           |

- ## ▶ Run
  ```
  npm run test:light
  ```

- ## 📈 Generate HTML Report
  ```
  npm run test:light-report
  ```

## 🔴 Heavy Load Test
- ### 🎯 Purpose

  Stress test transaction throughput

  Measure max sustainable RPS

  Identify DB locks, bottlenecks, and scaling limits

  Validate system behavior under extreme load


- ## 📄 File
  ```
  heavy-load-test.yml
  ```

- ## 🚦 Load Profile (example)
  ```
  phases:
    - duration: 120
      arrivalRate: 500
      rampTo: 3000
    - duration: 900
      arrivalRate: 3000
  ```

- ## 📊 Expected Numbers
  | Metric                    | Approximate Value |
  | ------------------------- | ----------------- |
  | Requests per second (RPS) | ~3,000            |
  | Test duration             | ~17 minutes       |
  | Total requests            | ~3,000,000        |
  | Concurrent users          | Thousands         |
  | CPU usage                 | High              |
  | DB contention             | High              |
  | Network usage             | High              |

- ## ▶ Run
  ```
  ARTILLERY_WORKERS=8 npm run test:heavy
  ```

- ## 📈 Generate HTML Report
  ```
  npm run test:heavy-report
  ```

- ## ⚙️ System Requirements for Heavy Load

  Before running heavy-load, you MUST raise system limits.

  Increase open file limit

  ```
    ulimit -n 100000
  ```