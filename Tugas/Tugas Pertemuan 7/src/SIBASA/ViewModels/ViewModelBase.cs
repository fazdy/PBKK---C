using CommunityToolkit.Mvvm.ComponentModel;

namespace SIBASA.ViewModels;

/// <summary>
/// Dasar semua ViewModel. ObservableObject (CommunityToolkit.Mvvm) mengimplementasikan
/// INotifyPropertyChanged sehingga perubahan properti langsung tampil di View lewat binding.
/// </summary>
public abstract class ViewModelBase : ObservableObject
{
}
