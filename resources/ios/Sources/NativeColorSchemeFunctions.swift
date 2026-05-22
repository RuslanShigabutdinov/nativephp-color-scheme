import Foundation
import UIKit

enum NativeColorSchemeFunctions {
    class Get: BridgeFunction {
        func execute(parameters: [String: Any]) throws -> [String: Any] {
            let style: UIUserInterfaceStyle

            if Thread.isMainThread {
                style = Self.currentInterfaceStyle()
            } else {
                style = DispatchQueue.main.sync {
                    Self.currentInterfaceStyle()
                }
            }

            let isDark = style == .dark
            let colorScheme = isDark ? "dark" : "light"

            return BridgeResponse.success(data: [
                "colorScheme": colorScheme,
                "isDark": isDark,
                "isLight": !isDark,
            ])
        }

        private static func currentInterfaceStyle() -> UIUserInterfaceStyle {
            let windowScene = UIApplication.shared.connectedScenes
                .compactMap { $0 as? UIWindowScene }
                .first

            return windowScene?.windows.first?.traitCollection.userInterfaceStyle
                ?? UIScreen.main.traitCollection.userInterfaceStyle
        }
    }
}
