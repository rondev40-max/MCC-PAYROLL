package com.mcc.payroll.data.local

import android.content.Context
import androidx.datastore.core.DataStore
import androidx.datastore.preferences.core.Preferences
import androidx.datastore.preferences.core.edit
import androidx.datastore.preferences.core.stringPreferencesKey
import androidx.datastore.preferences.preferencesDataStore
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.first
import kotlinx.coroutines.flow.map

private val Context.dataStore: DataStore<Preferences> by preferencesDataStore(name = "mcc_session")

/**
 * The signed-in session: bearer token plus enough of the user to render a
 * greeting before the first network call returns.
 *
 * Excluded from cloud backup and device transfer (see res/xml/backup_rules.xml)
 * — a restored token would be a working session on someone else's device.
 */
class SessionStore(private val context: Context) {

    private object Keys {
        val TOKEN = stringPreferencesKey("auth_token")
        val NAME = stringPreferencesKey("user_name")
        val EMAIL = stringPreferencesKey("user_email")
    }

    val token: Flow<String?> = context.dataStore.data.map { it[Keys.TOKEN] }
    val name: Flow<String?> = context.dataStore.data.map { it[Keys.NAME] }
    val email: Flow<String?> = context.dataStore.data.map { it[Keys.EMAIL] }

    /** One-shot read, for the OkHttp interceptor. */
    suspend fun currentToken(): String? = token.first()

    suspend fun save(token: String, name: String?, email: String?) {
        context.dataStore.edit { prefs ->
            prefs[Keys.TOKEN] = token
            prefs[Keys.NAME] = name.orEmpty()
            prefs[Keys.EMAIL] = email.orEmpty()
        }
    }

    suspend fun clear() {
        context.dataStore.edit { it.clear() }
    }
}
