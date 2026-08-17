package com.mcc.payroll

import android.app.Application
import com.mcc.payroll.data.local.SessionStore
import com.mcc.payroll.data.remote.ApiClient
import com.mcc.payroll.data.repo.EmployeeRepository

/**
 * Hand-rolled service locator.
 *
 * A five-screen app does not need Hilt: one Application-scoped graph built here
 * keeps the wiring visible in one place and costs nothing at startup. The
 * ordering matters — the API client needs the session store, because its auth
 * interceptor reads the token from it.
 */
class MccApp : Application() {

    lateinit var session: SessionStore
        private set

    lateinit var repository: EmployeeRepository
        private set

    override fun onCreate() {
        super.onCreate()
        session = SessionStore(this)
        repository = EmployeeRepository(ApiClient.create(session), session)
    }
}
