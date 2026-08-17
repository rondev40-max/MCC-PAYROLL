package com.mcc.payroll.data.remote

import com.mcc.payroll.BuildConfig
import com.mcc.payroll.data.local.SessionStore
import kotlinx.coroutines.runBlocking
import okhttp3.Interceptor
import okhttp3.OkHttpClient
import okhttp3.Response
import okhttp3.logging.HttpLoggingInterceptor
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory
import java.util.concurrent.TimeUnit

/**
 * Attaches the Sanctum bearer token to every request.
 *
 * runBlocking is acceptable here and only here: OkHttp interceptors are
 * synchronous by contract and already run off the main thread on OkHttp's
 * dispatcher, so this blocks a background worker, never the UI.
 */
class AuthInterceptor(private val session: SessionStore) : Interceptor {
    override fun intercept(chain: Interceptor.Chain): Response {
        val token = runBlocking { session.currentToken() }

        val request = chain.request().newBuilder()
            .header("Accept", "application/json")
            .apply { if (!token.isNullOrBlank()) header("Authorization", "Bearer $token") }
            .build()

        return chain.proceed(request)
    }
}

object ApiClient {

    /**
     * Built once the session store exists, because the interceptor needs it.
     * [create] is called from MccApp so the whole app shares one client and one
     * connection pool.
     */
    fun create(session: SessionStore): ApiService {
        val logging = HttpLoggingInterceptor().apply {
            // Request bodies carry passwords and responses carry payslip figures.
            // BODY-level logging is fine while debugging and must never ship.
            level = if (BuildConfig.DEBUG) {
                HttpLoggingInterceptor.Level.BODY
            } else {
                HttpLoggingInterceptor.Level.NONE
            }
        }

        val client = OkHttpClient.Builder()
            .addInterceptor(AuthInterceptor(session))
            .addInterceptor(logging)
            .connectTimeout(20, TimeUnit.SECONDS)
            .readTimeout(20, TimeUnit.SECONDS)
            .writeTimeout(20, TimeUnit.SECONDS)
            .retryOnConnectionFailure(true)
            .build()

        return Retrofit.Builder()
            .baseUrl(BuildConfig.API_BASE_URL)
            .client(client)
            .addConverterFactory(GsonConverterFactory.create())
            .build()
            .create(ApiService::class.java)
    }
}
