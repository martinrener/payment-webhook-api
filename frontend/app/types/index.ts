/**
 * Represents a single payment record returned by GET /api/payments.
 * Corresponds to the Laravel Payment Eloquent model serialized to JSON.
 */
export interface Payment {
  id: number
  payment_id: string
  event: string
  amount: number
  currency: string
  user_id: string
  last_event_id: string
  created_at: string
  updated_at: string
}

/**
 * Represents a single event log entry returned by GET /api/payments/{id}/events.
 * Corresponds to the Laravel EventLog Eloquent model (no timestamps columns).
 */
export interface EventLog {
  id: number
  event_id: string
  payment_id: string
  event: string
  amount: number
  currency: string
  user_id: string
  timestamp: string
  received_at: string
}

/**
 * Represents one item in the payments_by_event array of the metrics response.
 * Produced by COUNT(*) GROUP BY event in EloquentMetricsRepository.
 */
export interface PaymentsByEvent {
  event: string
  total: number
}

/**
 * Represents one item in the payments_by_currency array of the metrics response.
 * Produced by COUNT(*) GROUP BY currency in EloquentMetricsRepository.
 */
export interface PaymentsByCurrency {
  currency: string
  total: number
}

/**
 * Represents one item in the volume_by_day array of the metrics response.
 * Produced by SUM(amount) GROUP BY DATE(created_at), currency.
 * date is an ISO date string in 'YYYY-MM-DD' format.
 */
export interface VolumeByDay {
  date: string
  currency: string
  total: number
}

/**
 * Represents the full response body of GET /api/metrics.
 * Composed using the sub-interfaces above.
 */
export interface Metrics {
  payments_by_event: PaymentsByEvent[]
  unique_users_count: number
  payments_by_currency: PaymentsByCurrency[]
  volume_by_day: VolumeByDay[]
}
