package com.ruslanshigabutdinov.nativecolorscheme

import android.content.Context
import android.content.res.Configuration
import com.nativephp.mobile.bridge.BridgeFunction
import com.nativephp.mobile.bridge.BridgeResponse

object NativeColorSchemeFunctions {
    class Get(private val context: Context) : BridgeFunction {
        override fun execute(parameters: Map<String, Any>): Map<String, Any> {
            val nightMode = context.resources.configuration.uiMode and Configuration.UI_MODE_NIGHT_MASK
            val isDark = nightMode == Configuration.UI_MODE_NIGHT_YES
            val colorScheme = if (isDark) "dark" else "light"

            return BridgeResponse.success(
                mapOf(
                    "colorScheme" to colorScheme,
                    "isDark" to isDark,
                    "isLight" to !isDark
                )
            )
        }
    }
}
