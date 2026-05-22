package com.ruslanshigabutdinov.nativecolorscheme

import android.app.UiModeManager
import android.content.Context
import android.content.res.Configuration
import android.os.Build
import com.nativephp.mobile.bridge.BridgeFunction
import com.nativephp.mobile.bridge.BridgeError
import com.nativephp.mobile.bridge.BridgeResponse

private const val PREFERENCES_NAME = "native_color_scheme"
private const val PREFERENCE_KEY = "preference"
private const val PREFERENCE_SYSTEM = "system"
private const val PREFERENCE_LIGHT = "light"
private const val PREFERENCE_DARK = "dark"

fun initializeNativeColorScheme(context: Context) {
    NativeColorSchemeStore.applyPreference(context)
}

object NativeColorSchemeFunctions {
    class Get(private val context: Context) : BridgeFunction {
        override fun execute(parameters: Map<String, Any>): Map<String, Any> {
            return BridgeResponse.success(NativeColorSchemeStore.state(context))
        }
    }

    class SetPreference(private val context: Context) : BridgeFunction {
        override fun execute(parameters: Map<String, Any>): Map<String, Any> {
            val preference = NativeColorSchemeStore.normalizePreference(parameters["preference"])
                ?: throw BridgeError.InvalidParameters("preference must be system, light, or dark")

            NativeColorSchemeStore.setPreference(context, preference)

            return BridgeResponse.success(NativeColorSchemeStore.state(context))
        }
    }
}

private object NativeColorSchemeStore {
    fun state(context: Context): Map<String, Any> {
        val preference = getPreference(context)
        val systemColorScheme = getSystemColorScheme(context)
        val colorScheme = when (preference) {
            PREFERENCE_LIGHT -> PREFERENCE_LIGHT
            PREFERENCE_DARK -> PREFERENCE_DARK
            else -> systemColorScheme
        }
        val isDark = colorScheme == PREFERENCE_DARK

        return mapOf(
            "preference" to preference,
            "colorScheme" to colorScheme,
            "systemColorScheme" to systemColorScheme,
            "isSystem" to (preference == PREFERENCE_SYSTEM),
            "isDark" to isDark,
            "isLight" to !isDark
        )
    }

    fun getPreference(context: Context): String {
        val preferences = context.getSharedPreferences(PREFERENCES_NAME, Context.MODE_PRIVATE)

        return normalizePreference(preferences.getString(PREFERENCE_KEY, PREFERENCE_SYSTEM)) ?: PREFERENCE_SYSTEM
    }

    fun setPreference(context: Context, preference: String) {
        context.getSharedPreferences(PREFERENCES_NAME, Context.MODE_PRIVATE)
            .edit()
            .putString(PREFERENCE_KEY, preference)
            .commit()

        applyPreference(context, preference)
    }

    fun applyPreference(context: Context, preference: String = getPreference(context)) {
        if (Build.VERSION.SDK_INT < Build.VERSION_CODES.S) {
            return
        }

        val mode = when (preference) {
            PREFERENCE_LIGHT -> UiModeManager.MODE_NIGHT_NO
            PREFERENCE_DARK -> UiModeManager.MODE_NIGHT_YES
            else -> UiModeManager.MODE_NIGHT_AUTO
        }

        context.getSystemService(UiModeManager::class.java)?.setApplicationNightMode(mode)
    }

    fun normalizePreference(value: Any?): String? {
        val preference = value?.toString()?.trim()?.lowercase() ?: return null

        return when (preference) {
            "auto", "device", "os", PREFERENCE_SYSTEM -> PREFERENCE_SYSTEM
            PREFERENCE_LIGHT -> PREFERENCE_LIGHT
            PREFERENCE_DARK -> PREFERENCE_DARK
            else -> null
        }
    }

    private fun getSystemColorScheme(context: Context): String {
        val nightMode = context.resources.configuration.uiMode and Configuration.UI_MODE_NIGHT_MASK

        return if (nightMode == Configuration.UI_MODE_NIGHT_YES) PREFERENCE_DARK else PREFERENCE_LIGHT
    }
}
