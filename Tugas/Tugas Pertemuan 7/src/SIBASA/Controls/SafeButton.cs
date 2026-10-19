using Avalonia.Automation.Peers;
using Avalonia.Automation.Provider;
using Avalonia.Controls;

namespace SIBASA.Controls;

/// <summary>
/// Button biasa dengan perlindungan untuk aksesibilitas (VoiceOver/otomasi).
/// Pada Avalonia di macOS, menekan tombol NONAKTIF lewat aksesibilitas melempar
/// ElementNotEnabledException dari jalur native yang tidak bisa ditangkap, sehingga aplikasi tertutup.
/// SafeButton mengabaikan permintaan itu dengan aman. Tampilan dan gayanya sama persis dengan Button.
/// </summary>
public class SafeButton : Button
{
    // Pakai gaya (style/theme) milik Button, jadi selector seperti "Button.primary" tetap berlaku.
    protected override Type StyleKeyOverride => typeof(Button);

    protected override AutomationPeer OnCreateAutomationPeer() => new SafeButtonAutomationPeer(this);

    private sealed class SafeButtonAutomationPeer : ButtonAutomationPeer, IInvokeProvider
    {
        public SafeButtonAutomationPeer(Button owner) : base(owner)
        {
        }

        void IInvokeProvider.Invoke()
        {
            if (IsEnabled())
            {
                Invoke();
            }
        }
    }
}
