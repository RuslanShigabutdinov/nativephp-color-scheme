import Foundation
import UIKit

enum NativeColorSchemeFunctions {
    static func initialize() {
        NativeColorSchemeStore.applyPreference()
    }

    class Get: BridgeFunction {
        func execute(parameters: [String: Any]) throws -> [String: Any] {
            return BridgeResponse.success(data: NativeColorSchemeStore.state())
        }
    }

    class SetPreference: BridgeFunction {
        func execute(parameters: [String: Any]) throws -> [String: Any] {
            guard let preference = NativeColorSchemeStore.normalizePreference(parameters["preference"]) else {
                throw BridgeError.invalidParameters("preference must be system, light, or dark")
            }

            NativeColorSchemeStore.setPreference(preference)

            return BridgeResponse.success(data: NativeColorSchemeStore.state())
        }
    }
}

private enum NativeColorSchemeStore {
    private static let preferenceKey = "native_color_scheme_preference"
    private static let systemPreference = "system"
    private static let lightPreference = "light"
    private static let darkPreference = "dark"

    static func state() -> [String: Any] {
        let preference = getPreference()
        let systemColorScheme = getSystemColorScheme()
        let colorScheme: String

        switch preference {
        case lightPreference:
            colorScheme = lightPreference
        case darkPreference:
            colorScheme = darkPreference
        default:
            colorScheme = systemColorScheme
        }

        let isDark = colorScheme == darkPreference

        return [
            "preference": preference,
            "colorScheme": colorScheme,
            "systemColorScheme": systemColorScheme,
            "isSystem": preference == systemPreference,
            "isDark": isDark,
            "isLight": !isDark,
        ]
    }

    static func getPreference() -> String {
        normalizePreference(UserDefaults.standard.string(forKey: preferenceKey)) ?? systemPreference
    }

    static func setPreference(_ preference: String) {
        UserDefaults.standard.set(preference, forKey: preferenceKey)
        applyPreference(preference)
    }

    static func applyPreference() {
        applyPreference(getPreference())
    }

    static func applyPreference(_ preference: String) {
        let style: UIUserInterfaceStyle

        switch preference {
        case lightPreference:
            style = .light
        case darkPreference:
            style = .dark
        default:
            style = .unspecified
        }

        DispatchQueue.main.async {
            UIApplication.shared.connectedScenes
                .compactMap { $0 as? UIWindowScene }
                .flatMap { $0.windows }
                .forEach { $0.overrideUserInterfaceStyle = style }
        }
    }

    static func normalizePreference(_ value: Any?) -> String? {
        guard let rawValue = value as? String else {
            return nil
        }

        switch rawValue.trimmingCharacters(in: .whitespacesAndNewlines).lowercased() {
        case "auto", "device", "os", systemPreference:
            return systemPreference
        case lightPreference:
            return lightPreference
        case darkPreference:
            return darkPreference
        default:
            return nil
        }
    }

    private static func getSystemColorScheme() -> String {
        let style: UIUserInterfaceStyle

        if Thread.isMainThread {
            style = currentInterfaceStyle()
        } else {
            style = DispatchQueue.main.sync {
                currentInterfaceStyle()
            }
        }

        return style == .dark ? darkPreference : lightPreference
    }

    private static func currentInterfaceStyle() -> UIUserInterfaceStyle {
        let windowScene = UIApplication.shared.connectedScenes
            .compactMap { $0 as? UIWindowScene }
            .first

        return windowScene?.windows.first?.traitCollection.userInterfaceStyle
            ?? UIScreen.main.traitCollection.userInterfaceStyle
    }
}
